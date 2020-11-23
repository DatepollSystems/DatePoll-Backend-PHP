<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class BroadcastPermissionDenied extends ADatePollMailable {
  use Queueable, SerializesModels;

  /**
   * Create a new message instance
   */
  public function __construct() {
    parent::__construct('broadcastPermissionDenied');
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this
      ->subject('» DatePoll Verteiler - Fehler beim versenden der E-Mail')
      ->view('emails.broadcastPermissionDenied.broadcastPermissionDenied')
      ->text('emails.broadcastPermissionDenied.broadcastPermissionDenied_plain');
  }
}
