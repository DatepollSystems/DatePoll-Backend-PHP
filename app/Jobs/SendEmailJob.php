<?php

namespace App\Jobs;

use App\Logging;
use App\Mail\ADatePollMailable;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\BroadcastUserInfo;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @property ADatePollMailable $mailable
 * @property string[] $emailAddresses
 */
class SendEmailJob extends Job
{
  private ADatePollMailable $mailable;
  private array $emailAddresses;

  public int $userId;
  public int $broadcastId;

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
    $emailAddressString = '';
    foreach ($this->emailAddresses as $emailAddress) {
      $emailAddressString .= $emailAddress . ', ';
    }

    $broadcastUserInfo = null;
    if ($this->mailable instanceof BroadcastMail) {
      $broadcastUserInfo = BroadcastUserInfo::where('user_id', '=', $this->userId)->where('broadcast_id', '=', $this->broadcastId)->first();
      if ($broadcastUserInfo == null) {
        Logging::info($this->mailable->jobDescription, 'DELETED BROADCAST: Sent to ' . $emailAddressString . ' cancelled!');
        return;
      }
    }
    
    Mail::bcc($this->emailAddresses)
        ->send($this->mailable);
    Logging::info($this->mailable->jobDescription, 'Sent to ' . $emailAddressString);

    if ($broadcastUserInfo != null) {
      $broadcastUserInfo->sent = true;
      $broadcastUserInfo->save();
    }
  }
}
