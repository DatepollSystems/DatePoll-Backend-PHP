<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class ADatePollMailable extends Mailable {
  use Queueable, SerializesModels;

  public $jobDescription;

  /**
   * Create a new message instance.
   *
   * @param string $jobDescription
   */
  public function __construct(string $jobDescription) {
    $this->jobDescription = $jobDescription;
  }
}
