<?php

namespace App\Jobs;

use App\Models\User\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @property Mailable $mailable
 * @property string[] $emailAddresses
 */
class SendEmailJob extends Job
{
  private $mailable;
  private $emailAddresses;

  /**
   * Create a new job instance.
   *
   * @param Mailable $mailable
   * @param string[] $emailAddresses
   */
    public function __construct(Mailable $mailable, array $emailAddresses)
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
    }
}
