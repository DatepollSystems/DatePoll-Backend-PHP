<?php namespace App\Console\Commands;

use App\Utils\EnvironmentHelper;
use App\Utils\Generator;

class SetupDatePoll extends ACommand {
  protected $signature = 'setup-datepoll';
  protected $description = 'Set up process for DatePoll';

  public function __construct() {
    parent::__construct();
  }

  /**
   * @return void
   */
  public function handle() {
    $this->alert('Welcome to the DatePoll setup process!');
    $this->line('Lets start with the mail connection.');

    $this->make('MAIL_DRIVER', 'Please enter the mail driver', 'smtp');
    $this->make('MAIL_HOST', 'Please enter the mail host', 'mail.example.at');
    $this->make('MAIL_PORT', 'Please enter the mail (sending) port', '587');
    $this->make('MAIL_INCOMING_PORT', 'Please enter the mail (receiving) port', '993');
    $this->make('MAIL_ENCRYPTION', 'Please enter the mail encryption', 'tls');
    $this->make('MAIL_USERNAME', 'Please enter the mail username', 'datepoll@mail.example.at');
    $this->make('MAIL_PASSWORD', 'Please enter the mail password', 'super_secret_password');
    $this->make('MAIL_FROM_ADDRESS', 'Please enter the mail from address', 'datepoll@mail.example.at');
    $this->make('MAIL_REPLY_ADDRESS', 'Please enter the mail reply address', 'datepoll@mail.example.at');
    $this->make('MAIL_FROM_NAME', 'Please enter the mail sender name', 'DatePoll');

    $this->line('Now some security related things.');

    $this->make('APP_KEY', 'Please enter an app key (for the hash function)', Generator::getRandomMixedNumberAndABCToken(64));
    $this->make('JWT_SECRET', 'Please enter an jwt secret (for secure login)', Generator::getRandomMixedNumberAndABCToken(64));

    $this->comment('Finished with setting up the env file!');
  }

  private function make(string $envKeyWord, string $question, string $default) {
    $answer = $this->askStringQuestion($question, $default);

    EnvironmentHelper::setEnvironmentVariable($envKeyWord, $answer);
  }
}
