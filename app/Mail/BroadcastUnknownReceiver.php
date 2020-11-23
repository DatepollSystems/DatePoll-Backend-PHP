<?php

namespace App\Mail;

use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastUnknownReceiver extends ADatePollMailable
{
  use Queueable, SerializesModels;

  public string $receiverName;

  /**
   * Create a new message instance
   * @param string $receiverName
   */
  public function __construct(string $receiverName)
  {
    parent::__construct('broadcastUnknownReceiver');
    $this->receiverName = $receiverName;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this
      ->subject('» DatePoll Verteiler - Fehler beim versenden der E-Mail')
      ->view('emails.broadcastUnknownReceiver.broadcastUnknownReceiver')
      ->text('emails.broadcastUnknownReceiver.broadcastUnknownReceiver_plain');
  }
}
