<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection PhpRedundantCatchClauseInspection */

namespace App\Console\Commands;

use App\Logging;
use App\Mail\BroadcastInvalidSubject;
use App\Mail\BroadcastMail;
use App\Mail\BroadcastPermissionDenied;
use App\Mail\BroadcastUnknownReceiver;
use App\Models\Broadcasts\BroadcastAttachment;
use App\Permissions;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Utils\ArrayHelper;
use App\Utils\Converter;
use App\Utils\MailHelper;
use App\Utils\StringHelper;
use Exception;
use ForceUTF8\Encoding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\Pure;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use RuntimeException;

class ProcessBroadcastEmailsInInbox extends Command {
  /**
   * @var string[]
   */
  private static array $allKeywords = ['Alle', 'All', 'Mitglieder', 'Everyone'];

  private static string $actionDelete = 'delete';
  private static string $actionCancelProcessing = 'cancelProcessing';

  /**
   * Block list with sender email addresses which should be ignored
   * Add DatePoll from address to array in constructor.
   * @var string[]
   */
  private array $senderBlockList = ['mailer-daemon@mail.itkfm.at'];

  protected $signature = 'process-inbox-broadcast-mails';
  protected $description = 'Processes all emails!';

  public function __construct(
    private IUserRepository $userRepository,
    private IBroadcastRepository $broadcastRepository,
    private IGroupRepository $groupRepository,
    private IBroadcastAttachmentRepository $broadcastAttachmentRepository,
    private ISettingRepository $settingsRepository
  ) {
    parent::__construct();

    $this->senderBlockList[] = StringHelper::toLowerCase(env('MAIL_FROM_ADDRESS'));
  }

  /**
   * @throws InvalidParameterException
   * @throws Exception
   */
  public function handle(): void {
    if (! $this->settingsRepository->getBroadcastsProcessIncomingEmailsEnabled() || ! $this->settingsRepository->getBroadcastsEnabled()) {
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
    }

    Logging::info('processBroadcastEmails', 'Emails found to process');

    foreach ($mailsIds as $mailId) {
      $mail = $mailbox->getMail($mailId);

      $response = $this->processEmail($mail);
      switch ($response) {
        case self::$actionDelete:
          $mailbox->deleteMail($mailId);
          break;
        case self::$actionCancelProcessing:
          return;
        default:
          throw new RuntimeException('Unknown action encountered: ' . $response);
      }
    }
  }

  /**
   * @param IncomingMail $mail
   * @return string
   */
  private function processEmail(IncomingMail $mail): string {
    $subject = $mail->subject;
    $fromAddress = $mail->senderAddress;
    Logging::info(
      'processBroadcastEmails',
      'Email to process: "' . $subject . '" from "' . $fromAddress . '"'
    );

    // Check if from address is in block list
    if (ArrayHelper::inArray($this->senderBlockList, StringHelper::toLowerCase($fromAddress))) {
      Logging::info('processBroadcastEmails', 'Got an email from DatePoll and deleting it: "' . $subject . '"');

      return self::$actionDelete;
    }

    if (! $this->isBroadcastSubjectValid($subject)) {
      if ($this->settingsRepository->getBroadcastsProcessIncomingEmailsForwardingEnabled()) {
        Logging::info('processBroadcastEmails', 'Forwarding email to community major...');

        $this->forwardEmailToDatePollCommunityLeaders($mail, $subject, $fromAddress);

        return self::$actionDelete;
      }

      Logging::info('processBroadcastEmails', $fromAddress . ' Subject not valid. Subject: "' . $subject . '"');
      MailHelper::sendEmailOnHighQueue(new BroadcastInvalidSubject(), $fromAddress);

      return self::$actionDelete;
    }

    $userId = null;
    foreach ($this->userRepository->getUsersByEmailAddress($fromAddress) as $user) {
      if ($user->hasPermission(Permissions::$BROADCASTS_ADMINISTRATION)) {
        $userId = $user->id;
        break;
      }
    }

    if ($userId == null) {
      if ($this->settingsRepository->getBroadcastsProcessIncomingEmailsForwardingEnabled()) {
        Logging::info(
          'processBroadcastEmails',
          $fromAddress . ' Permission denied, forwarding it. Subject: "' . $subject . '"'
        );

        $this->forwardEmailToDatePollCommunityLeaders($mail, $subject, $fromAddress);

        return self::$actionDelete;
      }

      Logging::info(
        'processBroadcastEmails',
        $fromAddress . ' Permission denied, deleting it. Subject: "' . $subject . '"'
      );
      MailHelper::sendEmailOnHighQueue(new BroadcastPermissionDenied(), $fromAddress);

      return self::$actionDelete;
    }

    $textPlain = $mail->textPlain;
    $textHtml = $textPlain;
    if (StringHelper::notNullAndEmpty($mail->textHtml)) {
      $textHtml = $mail->textHtml;
    }
    $textPlain = StringHelper::removeImageHtmlTag(Encoding::toUTF8($textPlain));
    $textHtml = StringHelper::removeImageHtmlTag(Encoding::toUTF8($textHtml));

    $attachmentIds = [];
    Logging::info(
      'processBroadcastEmails',
      'Attachments: ' . Converter::booleanToString($mail->hasAttachments()) . '; Count: ' . ArrayHelper::getSize($mail->getAttachments())
    );
    foreach ($mail->getAttachments() as $attachment) {
      $token = $this->broadcastAttachmentRepository->getUniqueRandomBroadcastAttachmentToken();
      $attachment->setFilePath(Storage::disk('local')->path('files/') . $token);

      Logging::info('processBroadcastEmails@savingAttachments', 'Attachment name: ' . $attachment->name . '; Path: ' . $attachment->filePath);

      $attachmentModel = new BroadcastAttachment(['path' => 'files/' . $token, 'name' => $attachment->name, 'token' => $token]);
      if (! $attachmentModel->save() || ! $attachment->saveToDisk()) {
        Logging::error('processBroadcastEmails@savingAttachments', 'Could not save attachment!');

        return self::$actionCancelProcessing;
      }

      $attachmentIds[] = $attachmentModel->id;
    }

    $forEveryone = $this->containsSendToAllKeyword($subject);
    $groupsIds = [];
    if (! $forEveryone) {
      $groupsIds = $this->getGroupIdsOfSubject($subject, $fromAddress);
      if ($groupsIds == null) {
        return self::$actionDelete;
      }
    }

    Logging::info(
      'processBroadcastEmails',
      'Sending email. Creating broadcast... Subject: "' . $subject . '"'
    );

    $this->broadcastRepository->create(
      $subject,
      $textHtml,
      $textPlain,
      $userId,
      $groupsIds,
      [],
      $forEveryone,
      $attachmentIds
    );

    return self::$actionDelete;
  }

  private function forwardEmailToDatePollCommunityLeaders(
    IncomingMail $mail,
    string $subject,
    string $fromAddress
  ): void {
    $textPlain = $mail->textPlain;
    $textHtml = $textPlain;
    if (StringHelper::notNullAndEmpty($mail->textHtml)) {
      $textHtml = $mail->textHtml;
    }
    $textPlain = StringHelper::removeImageHtmlTag(Encoding::toUTF8($textPlain));
    $textHtml = StringHelper::removeImageHtmlTag(Encoding::toUTF8($textHtml));

    $broadcastMail = new BroadcastMail(
      $subject,
      $textPlain,
      $textHtml,
      $mail->senderName ?: $fromAddress,
      $fromAddress,
      $this->settingsRepository->getUrl(),
      ''
    );
    MailHelper::sendEmailOnHighQueue(
      $broadcastMail,
      $this->settingsRepository->getBroadcastsProcessIncomingEmailsForwardingEmailAddresses()
    );
  }

  /**
   * @param string $subject
   * @param string $fromAddress
   * @return array|null
   */
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

  /**
   * @param string $subject
   * @return bool
   */
  #[Pure]
  private function containsSendToAllKeyword(string $subject): bool {
    $receiver = $this->getReceiverString($subject);
    foreach (self::$allKeywords as $keyword) {
      if (StringHelper::contains($receiver, $keyword)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string|null $subject
   * Possible subjects:
   *  - "[All]Test"
   *  - "[Leaders,Dancers] Test"
   *  - "[Leaders, Dancers] Test in test"
   *  - "[Mitglieder] "
   * @return bool
   */
  private function isBroadcastSubjectValid(?string $subject): bool {
    if (StringHelper::null($subject)) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Subject null');

      return false;
    }

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

    if (! StringHelper::startsWithCharacter($subject, '[')) {
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
    if (StringHelper::length($receiverString) < 1 || StringHelper::nullAndEmpty($receiverString)) {
      Logging::info('processBroadcastEmails', 'Broadcast subject invalid. Nothing between "[" and "]"');

      return false;
    }

    return true;
  }

  #[Pure]
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
