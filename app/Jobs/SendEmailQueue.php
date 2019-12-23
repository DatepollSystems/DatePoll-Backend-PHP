<?php

namespace App\Jobs;

use App\Models\User\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendEmailQueue
 * @package App\Jobs
 * @property Mailable $mailable
 */
class SendEmailQueue extends Job
{
  private $mailable;
  private $user;

  /**
   * Create a new job instance.
   *
   * @param Mailable $mailable
   * @param User $user
   */
    public function __construct(Mailable $mailable, User $user)
    {
        $this->mailable = $mailable;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      Mail::bcc($this->user->getEmailAddresses())
          ->send($this->mailable);
    }
}
