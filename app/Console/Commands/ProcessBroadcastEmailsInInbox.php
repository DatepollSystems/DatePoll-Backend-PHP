<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

/** @noinspection PhpRedundantCatchClauseInspection */

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\BroadcastInvalidSubject;
use App\Mail\BroadcastPermissionDenied;
use App\Mail\BroadcastUnknownReceiver;
use App\Permissions;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Group\Group\IGroupRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use PhpImap\Mailbox;

class ProcessBroadcastEmailsInInbox extends Command {
  private IBroadcastRepository $broadcastRepository;
  private IGroupRepository $groupRepository;

  protected $signature = 'process-inbox-broadcast-mails';
  protected $description = 'Processes all emails!';

  public function __construct(IBroadcastRepository $broadcastRepository, IGroupRepository $groupRepository) {
    parent::__construct();

    $this->broadcastRepository = $broadcastRepository;
    $this->groupRepository = $groupRepository;
  }

  /**
   * @throws InvalidParameterException
   */
  public function handle() {
    $mailbox = new Mailbox(
      '{' . env('MAIL_HOST') . ':' . env('MAIL_INCOMING_PORT') . '/imap/ssl}INBOX',
      env('MAIL_USERNAME'),
      env('MAIL_PASSWORD'),
      null, // Directory, where attachments will be saved (optional)
      'UTF-8' // Server encoding
    );

    try {
      $mailsIds = $mailbox->searchMailbox('ALL');
    } catch (ConnectionException $ex) {
      Logging::error('processBroadcastEmails', 'IMAP Connection failed. ' . $ex->getMessage());

      return;
    }

    // If $mailsIds is empty, no emails could be found
    if (! $mailsIds) {
      return;
    } else {
      Logging::info('processBroadcastEmails', 'Emails found to process');
    }

    foreach ($mailsIds as $mailId) {
      $mail = $mailbox->getMail($mailId);
      $subject = $mail->subject;
      $mailId = $mail->id;
      Logging::info(
        'processBroadcastEmails',
        'Email to process: "' . $subject . '" from "' . $mail->fromAddress . '"'
      );

      $userHasPermissionToSendBroadcasts = DB::table('user_email_addresses')
        ->join('user_permissions', 'user_email_addresses.user_id', '=', 'user_permissions.user_id')
        ->where('user_email_addresses.email', '=', $mail->fromAddress)
        ->where('user_permissions.permission', '=', Permissions::$BROADCASTS_ADMINISTRATION)
        ->orWhere('user_permissions.permission', '=', Permissions::$ROOT_ADMINISTRATION)
        ->select('user_permissions.user_id as user_id')->first();

      if ($userHasPermissionToSendBroadcasts != null) {
        if ($this->isBroadcastSubjectValid($subject)) {
          $textHtml = $mail->textPlain;
          if (! $this->IsNullOrEmptyString($mail->textHtml)) {
            $textHtml = $mail->textHtml;
          }

          if ($this->containsSendToAllKeyword($subject)) {
            Logging::info(
              'processBroadcastEmails',
              'All keyword detected, sending email to everyone. Creating broadcast...'
            );

            $this->broadcastRepository->create(
              $subject,
              $textHtml,
              $mail->textPlain,
              $userHasPermissionToSendBroadcasts->user_id,
              [],
              [],
              true,
              []
            );
          } else {
            $groupsIds = [];
            $receiverStrings = explode(',', $this->getReceiverString($subject));

            foreach ($receiverStrings as $receiverGroupName) {
              $foundSomething = false;
              foreach ($this->groupRepository->getAllGroupsOrdered() as $group) {
                if (strtolower(trim($group->name)) == strtolower(trim($receiverGroupName))) {
                  Logging::info('processBroadcastEmails', 'Receiver found: ' . $group->name);
                  $groupsIds[] = $group->id;
                  $foundSomething = true;
                }
              }

              if (! $foundSomething) {
                Logging::info('processBroadcastEmails', 'Unknown receiver specified in subject: ' . $receiverGroupName);
                dispatch(new SendEmailJob(
                  new BroadcastUnknownReceiver($receiverGroupName),
                  [$mail->fromAddress]
                ))->onQueue('high');
                $this->deleteMail($mailbox, $mailId);

                return;
              }
            }

            Logging::info('processBroadcastEmails', 'Creating broadcast...');
            $this->broadcastRepository->create(
              $subject,
              $textHtml,
              $mail->textPlain,
              $userHasPermissionToSendBroadcasts->user_id,
              $groupsIds,
              [],
              false,
              []
            );
          }
        } else {
          dispatch(new SendEmailJob(new BroadcastInvalidSubject(), [$mail->fromAddress]))->onQueue('high');
        }
      } else {
        Logging::info('processBroadcastEmails', 'Permission denied.');
        dispatch(new SendEmailJob(new BroadcastPermissionDenied(), [$mail->fromAddress]))->onQueue('high');
      }

      $this->deleteMail($mailbox, $mailId);
    }
  }

  private function deleteMail(Mailbox $mailbox, int $mailId) {
    $mailbox->deleteMail($mailId);
  }

  private function containsSendToAllKeyword(string $subject): bool {
    $receiver = $this->getReceiverString($subject);
    $keywords = ['Alle', 'All', 'Mitglieder', 'Everyone'];
    foreach ($keywords as $keyword) {
      if (str_contains($receiver, $keyword)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $subject
   * Possible subjects:
   *  - "[All]Test"
   *  - "[Leaders,Dancers] Test"
   *  - "[Leaders, Dancers] Test in test"
   *  - "[Mitglieder] "
   * @return bool
   */
  private function isBroadcastSubjectValid(string $subject): bool {
    // "[A]T" is the smallest possible valid email subject
    if (strlen($subject) < 4) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Length < 4');

      return false;
    }
    // Invalid: "[[All]] Wow" | "[All] [wow] Test"
    if (substr_count($subject, '[') > 1 || substr_count($subject, ']') > 1) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Contains two "[" or "]"');

      return false;
    }

    if (substr($subject, 0, 1) != '[') {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Does not start with "["');

      return false;
    }

    // Invalid: "[All]"
    if (strlen(explode(']', $subject)[1]) < 1) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. After "]" is not any text');

      return false;
    }

    // Invalid: "[]" | "[ ]"
    $receiverString = $this->getReceiverString($subject);
    if (strlen($receiverString) < 1 || $this->IsNullOrEmptyString($receiverString)) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Nothing between "[" and "]"');

      return false;
    }

    return true;
  }

  private function getReceiverString(string $string): string {
    $start = '[';
    $end = ']';

    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) {
      return '';
    }
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);
  }

  private function IsNullOrEmptyString($str) {
    return (! isset($str) || trim($str) === '');
  }
}
