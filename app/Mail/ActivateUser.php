<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivateUser extends Mailable
{
  use Queueable, SerializesModels;

  public $name;
  public $username;
  public $code;
  public $DatePollAddress;

  /**
   * Create a new message instance.
   *
   * @param $name
   * @param $code
   */
  public function __construct($name, $username, $code)
  {
    $this->DatePollAddress = env("APP_URL");
    $this->username = $username;
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
      ->subject('DatePoll Accountaktivierung')
      ->view('emails.userActivation.activateUser')
      ->text('emails.userActivation.activateUser_plain');
  }
}
