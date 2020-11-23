<?php

namespace App\Mail;

use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastPermissionDenied extends ADatePollMailable
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance
   */
  public function __construct()
  {
    parent::__construct('broadcastPermissionDenied');
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
      ->view('emails.broadcastPermissionDenied.broadcastPermissionDenied')
      ->text('emails.broadcastPermissionDenied.broadcastPermissionDenied_plain');
  }
}
