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
class SendEmailJob extends Job
{
  private $mailable;
  private $emailAddresses;

  /**
   * Create a new job instance.
   *
   * @param ADatePollMailable $mailable
   * @param string[] $emailAddresses
   */
    public function __construct(ADatePollMailable $mailable, array $emailAddresses)
    {
        $this->mailable = $mailable;
        $this->emailAddresses = $emailAddresses;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      Mail::bcc($this->emailAddresses)
          ->send($this->mailable);
      $emailAddressString = '';
      foreach ($this->emailAddresses as $emailAddress) {
        $emailAddressString .= $emailAddress . ', ';
      }
      Logging::info($this->mailable->jobDescription, 'Sent to ' . $emailAddressString);
    }
}
