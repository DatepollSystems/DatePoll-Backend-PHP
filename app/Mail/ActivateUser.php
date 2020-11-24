<?php

namespace App\Mail;

use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ActivateUser extends ADatePollMailable {
  use Queueable, SerializesModels;

  public $name;
  public $username;
  public $code;
  public $DatePollAddress;

  protected $settingRepository = null;

  /**
   * Create a new message instance.
   *
   * @param string $name
   * @param string $username
   * @param string $code
   * @param ISettingRepository $settingRepository
   */
  public function __construct($name, $username, $code, ISettingRepository $settingRepository) {
    parent::__construct('activateUser');

    $this->settingRepository = $settingRepository;

    $this->DatePollAddress = $this->settingRepository->getUrl();
    $this->username = $username;
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
      ->subject('» DatePoll Accountaktivierung')
      ->view('emails.userActivation.activateUser')
      ->text('emails.userActivation.activateUser_plain');
  }
}
