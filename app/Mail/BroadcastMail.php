<?php

namespace App\Mail;

use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class BroadcastMail extends ADatePollMailable
{
  use Queueable, SerializesModels;

  public $subject;
  public $body;
  public $bodyHTML;
  public $writerName;
  public $DatePollAddress;

  protected $settingRepository = null;

  /**
   * Create a new message instance.
   *
   * @param string $subject
   * @param string $body
   * @param string $bodyHTML
   * @param string $writerName
   * @param ISettingRepository $settingRepository
   */
  public function __construct($subject, $body, $bodyHTML, $writerName, ISettingRepository $settingRepository) {
    parent::__construct('broadcast');

    $this->settingRepository = $settingRepository;

    $this->DatePollAddress = $this->settingRepository->getUrl();
    $this->subject = $subject;
    $this->body = $body;
    $this->bodyHTML = $bodyHTML;
    $this->writerName = $writerName;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this->subject('» ' . $this->subject)
                ->from(env('MAIL_FROM_ADDRESS'), $this->writerName)
                ->view('emails.broadcast.broadcast')
                ->text('emails.broadcast.broadcast_plain');
  }
}
