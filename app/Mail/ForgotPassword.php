<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends ADatePollMailable {
  use Queueable, SerializesModels;

  public $name;
  public $code;

  /**
   * Create a new message instance.
   *
   * @param string $name
   * @param string $code
   */
  public function __construct($name, $code) {
    parent::__construct('forgotPassword');

    $this->name = $name;
    $this->code = $code;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this
      ->subject('» DatePoll Passwort-Reset')
      ->view('emails.forgotPassword.forgotPassword')
      ->text('emails.forgotPassword.forgotPassword_plain');
  }
}
