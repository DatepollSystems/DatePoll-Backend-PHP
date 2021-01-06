<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection PhpRedundantCatchClauseInspection */

namespace App\Console\Commands;

use App\Logging;
use App\Mail\BroadcastInvalidSubject;
use App\Mail\BroadcastPermissionDenied;
use App\Mail\BroadcastUnknownReceiver;
use App\Models\Broadcasts\BroadcastAttachment;
use App\Permissions;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Utils\MailHelper;
use App\Utils\StringHelper;
use ForceUTF8\Encoding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use PhpImap\Mailbox;

class ProcessBroadcastEmailsInInbox extends Command {
  /**
   * @var string[]
   */
  private static array $allKeywords = ['Alle', 'All', 'Mitglieder', 'Everyone'];

  protected $signature = 'process-inbox-broadcast-mails';
  protected $description = 'Processes all emails!';

  public function __construct(
    private IBroadcastRepository $broadcastRepository,
    private IGroupRepository $groupRepository,
    private IBroadcastAttachmentRepository $broadcastAttachmentRepository,
    private ISettingRepository $settingsRepository
  ) {
    parent::__construct();
  }

  /**
   * @throws InvalidParameterException
   */
  public function handle() {
    if (! $this->settingsRepository->getBroadcastsProcessIncomingEmailsEnabled()) {
      return;
    }

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
      $fromAddress = $mail->senderAddress;
      $mailId = $mail->id;
      Logging::info(
        'processBroadcastEmails',
        'Email to process: "' . $subject . '" from "' . $fromAddress . '"'
      );

      if (! StringHelper::compare(env('MAIL_FROM_ADDRESS'), $fromAddress)) {
        $userHasPermissionToSendBroadcasts = DB::table('user_email_addresses')
          ->join('user_permissions', 'user_email_addresses.user_id', '=', 'user_permissions.user_id')
          ->where('user_email_addresses.email', '=', $fromAddress)
          ->where('user_permissions.permission', '=', Permissions::$BROADCASTS_ADMINISTRATION)
          ->orWhere('user_permissions.permission', '=', Permissions::$ROOT_ADMINISTRATION)
          ->select('user_permissions.user_id as user_id')->first();

        if ($userHasPermissionToSendBroadcasts != null) {
          if ($this->isBroadcastSubjectValid($subject)) {
            $textPlain = $mail->textPlain;
            $textHtml = $textPlain;
            if (StringHelper::notNullAndEmpty($mail->textHtml)) {
              $textHtml = $mail->textHtml;
            }
            $textPlain = Encoding::toUTF8($textPlain);
            $textHtml = Encoding::toUTF8($textHtml);

            $attachmentIds = [];
            foreach ($mail->getAttachments() as $attachment) {
              $token = $this->broadcastAttachmentRepository->getUniqueRandomBroadcastAttachmentToken();
              $path = 'files/' . $token . '.' . $attachment->fileExtension;
              $attachmentModel = new BroadcastAttachment(['path' => $path, 'name' => $attachment->name, 'token' => $token]);
              if (! Storage::put($path, $attachment->getContents()) || ! $attachmentModel->save()) {
                Logging::error('processBroadcastEmails', 'Could not save attachment!');
                $this->deleteMail($mailbox, $mailId);

                return;
              }

              $attachmentIds[] = $attachmentModel->id;
            }

            if ($this->containsSendToAllKeyword($subject)) {
              Logging::info(
                'processBroadcastEmails',
                'All keyword detected, sending email to everyone. Creating broadcast...'
              );

              $this->broadcastRepository->create(
                $subject,
                $textHtml,
                $textPlain,
                $userHasPermissionToSendBroadcasts->user_id,
                [],
                [],
                true,
                $attachmentIds
              );
            } else {
              $groupsIds = $this->getGroupIdsOfSubject($subject, $fromAddress);
              if ($groupsIds != null) {
                Logging::info('processBroadcastEmails', 'Creating broadcast...');
                $this->broadcastRepository->create(
                  $subject,
                  $textHtml,
                  $textPlain,
                  $userHasPermissionToSendBroadcasts->user_id,
                  $groupsIds,
                  [],
                  false,
                  $attachmentIds
                );
              }
            }
          } else {
            MailHelper::sendEmailOnHighQueue(new BroadcastInvalidSubject(), $fromAddress);
          }
        } else {
          Logging::info('processBroadcastEmails', $fromAddress . ' Permission denied, deleting it. Subject: ' . $subject);
          MailHelper::sendEmailOnHighQueue(new BroadcastPermissionDenied(), $fromAddress);
        }
      } else {
        Logging::info('processBroadcastEmails', 'Got an email from DatePoll and deleting it: ' . $subject);
      }

      $this->deleteMail($mailbox, $mailId);
    }
  }

  private function deleteMail(Mailbox $mailbox, int $mailId) {
    $mailbox->deleteMail($mailId);
  }

  private function getGroupIdsOfSubject(string $subject, string $fromAddress): ?array {
    $groupsIds = [];
    $receiverStrings = explode(',', $this->getReceiverString($subject));

    foreach ($receiverStrings as $receiverGroupName) {
      $foundSomething = false;
      foreach ($this->groupRepository->getAllGroupsOrdered() as $group) {
        if (StringHelper::toLowerCaseWithTrim($group->name) == StringHelper::toLowerCaseWithTrim($receiverGroupName)) {
          Logging::info('processBroadcastEmails', 'Receiver found: ' . $group->name);
          $groupsIds[] = $group->id;
          $foundSomething = true;
        }
      }

      if (! $foundSomething) {
        Logging::info('processBroadcastEmails', 'Unknown receiver specified in subject: ' . $receiverGroupName);
        MailHelper::sendEmailOnHighQueue(new BroadcastUnknownReceiver($receiverGroupName), $fromAddress);

        return null;
      }
    }

    return $groupsIds;
  }

  private function containsSendToAllKeyword(string $subject): bool {
    $receiver = $this->getReceiverString($subject);
    foreach (ProcessBroadcastEmailsInInbox::$allKeywords as $keyword) {
      if (StringHelper::contains($receiver, $keyword)) {
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
    if (StringHelper::length($subject) < 4) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Length < 4');

      return false;
    }
    // Invalid: "[[All]] Wow" | "[All] [wow] Test"
    if (StringHelper::countSubstring($subject, '[') > 1 || StringHelper::countSubstring($subject, ']') > 1) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Contains two "[" or "]"');

      return false;
    }

    if (substr($subject, 0, 1) != '[') {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Does not start with "["');

      return false;
    }

    // Invalid: "[All]"
    if (StringHelper::length(explode(']', $subject)[1]) < 1) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. After "]" is not any text');

      return false;
    }

    // Invalid: "[]" | "[ ]"
    $receiverString = $this->getReceiverString($subject);
    if (StringHelper::length($receiverString) < 1 || ! StringHelper::notNullAndEmpty($receiverString)) {
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
    $ini += StringHelper::length($start);
    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);
  }
}
