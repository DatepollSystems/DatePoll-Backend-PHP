<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class BroadcastInvalidSubject extends ADatePollMailable {
  use Queueable, SerializesModels;

  /**
   * Create a new message instance
   */
  public function __construct() {
    parent::__construct('broadcastInvalidSubject');
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this
      ->subject('» DatePoll Verteiler - Fehler beim versenden der E-Mail')
      ->view('emails.broadcastInvalidSubject.broadcastInvalidSubject')
      ->text('emails.broadcastInvalidSubject.broadcastInvalidSubject_plain');
  }
}
