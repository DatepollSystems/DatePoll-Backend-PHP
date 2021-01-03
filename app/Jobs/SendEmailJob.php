<?php

namespace App\Jobs;

use App\Logging;
use App\Mail\ADatePollMailable;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @property ADatePollMailable $mailable
 * @property string[] $emailAddresses
 */
class SendEmailJob extends Job {
  protected ADatePollMailable $mailable;
  protected array $emailAddresses;
  protected string $emailAddressesString = '';

  /**
   * Create a new job instance.
   *
   * @param ADatePollMailable $mailable
   * @param string[] $emailAddresses
   */
  public function __construct(ADatePollMailable $mailable, array $emailAddresses) {
    $this->mailable = $mailable;
    $this->emailAddresses = $emailAddresses;

    foreach ($this->emailAddresses as $emailAddress) {
      $this->emailAddressesString .= $emailAddress . ', ';
    }
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle() {
    $this->sendEmail();
  }

  protected function sendEmail() {
    Mail::bcc($this->emailAddresses)
      ->send($this->mailable);
    Logging::info($this->mailable->jobDescription, 'Sent to ' . $this->emailAddressesString);
  }
}
