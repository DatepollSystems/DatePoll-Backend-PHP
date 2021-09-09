<?php

namespace App\Jobs;

use App\Logging;
use App\Mail\ADatePollMailable;
use Illuminate\Support\Facades\Mail;

class SendEmailJob extends Job {
  protected string $emailAddressesString = '';

  /**
   * @param ADatePollMailable $mailable
   * @param string[] $emailAddresses
   */
  public function __construct(protected ADatePollMailable $mailable, protected array $emailAddresses) {
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

  protected function sendEmail(): void {
    Mail::bcc($this->emailAddresses)
      ->send($this->mailable);
    Logging::info($this->mailable->jobDescription, 'Sent to ' . $this->emailAddressesString);
  }
}
