<?php

namespace App\Mail;

use App\Models\Broadcasts\BroadcastAttachment;
use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class BroadcastMail extends ADatePollMailable
{
  use Queueable, SerializesModels;

  public string $mSubject;
  public string $body;
  public string $bodyHTML;
  public string $writerName;
  public string $emailAddress;
  public string $DatePollAddress;
  public string $mAttachments = '';

  /**
   * Create a new message instance.
   *
   * @param string $subject
   * @param string $body
   * @param string $bodyHTML
   * @param string $writerName
   * @param string $emailAddress
   * @param string $DatePollAddress
   * @param string $mAttachments
   */
  public function __construct(string $subject, string $body, string $bodyHTML, string $writerName, string $emailAddress, string $DatePollAddress, string $mAttachments) {
    parent::__construct('broadcastSending');

    $this->DatePollAddress = $DatePollAddress;
    $this->mSubject = $subject;
    $this->body = $body;
    $this->bodyHTML = $bodyHTML;
    $this->writerName = $writerName;
    if ($emailAddress == null) {
      $emailAddress = env('MAIL_FROM_ADDRESS');
    }
    $this->emailAddress = $emailAddress;
    $this->mAttachments = $mAttachments;

  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this->subject('» ' . $this->mSubject)
                ->from(env('MAIL_FROM_ADDRESS'), $this->writerName)
                ->replyTo($this->emailAddress, $this->writerName)
                ->view('emails.broadcast.broadcast')
                ->text('emails.broadcast.broadcast_plain');
  }
}
