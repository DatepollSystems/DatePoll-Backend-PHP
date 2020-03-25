<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
  use Queueable, SerializesModels;

  public $name;
  public $code;

  /**
   * Create a new message instance.
   *
   * @param string $name
   * @param string $code
   */
  public function __construct($name, $code)
  {
    $this->name = $name;
    $this->code = $code;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this
      ->subject('» DatePoll Passwort-Reset')
      ->view('emails.forgotPassword.forgotPassword')
      ->text('emails.forgotPassword.forgotPassword_plain');
  }
}
