<?php

namespace App\Jobs;

use App\Logging;
use App\Mail\ADatePollMailable;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\BroadcastUserInfo;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @property ADatePollMailable $mailable
 * @property string[] $emailAddresses
 * @property string $emailAddressesString
 * @property int userId
 * @property int broadcastId
 */
class SendBroadcastEmailJob extends SendEmailJob {
  /**
   * Create a new job instance.
   *
   * @param BroadcastMail $mailable
   * @param string[] $emailAddresses
   * @param int $userId
   * @param int $broadcastId
   */
  public function __construct(BroadcastMail $mailable, array $emailAddresses, protected int $userId, protected int $broadcastId) {
    parent::__construct($mailable, $emailAddresses);
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle() {
    $broadcastUserInfo = BroadcastUserInfo::where('user_id', '=', $this->userId)->where('broadcast_id', '=', $this->broadcastId)->first();
    if ($broadcastUserInfo == null) {
      Logging::info($this->mailable->jobDescription, 'DELETED BROADCAST: Sent to ' . $this->emailAddressesString . ' cancelled!');

      return;
    }
    
    $this->sendEmail();

    $broadcastUserInfo->sent = true;
    $broadcastUserInfo->save();
  }
}
