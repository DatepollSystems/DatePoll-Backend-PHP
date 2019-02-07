<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewEmailVerifying extends Mailable
{
  use Queueable, SerializesModels;

  public $name;
  public $code;

  /**
   * Create a new message instance.
   *
   * @param $name
   * @param $code
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
      ->subject('Email-Adressen Änderung - Verifizierungscode für deine neue Email-Adresse')
      ->view('emails.emailChange.verifyNewEmailAddress')
      ->text('emails.emailChange.verifyNewEmailAddress_plain');
  }
}
