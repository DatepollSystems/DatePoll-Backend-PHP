<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserPermission;
use Illuminate\Console\Command;

class SetupDatePoll extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'setup-datepoll';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Set up process for DatePoll';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $this->alert('Welcome to the DatePoll setup process!');
    $this->line('Lets start with the database connection.');

    $this->make('TEST', 'Please enter the frontend url');
    $this->make('TEST', 'Please enter the database connection host');
    $this->make('TEST', 'Please enter the database connection port');
    $this->make('TEST', 'Please enter the database database');
    $this->make('TEST', 'Please enter the mail driver');
    $this->make('TEST', 'Please enter the mail host');
    $this->make('TEST', 'Please enter the mail port');
    $this->make('TEST', 'Please enter the mail encryption');
    $this->make('TEST', 'Please enter the mail username');
    $this->make('TEST', 'Please enter the mail password');
    $this->make('TEST', 'Please enter the mail from address');
    $this->make('TEST', 'Please enter the mail reply address');
    $this->make('TEST', 'Please enter the mail sender name');
    $this->make('TEST', 'Please enter an app key (for the hash function)');
    $this->make('TEST', 'Please enter an jwt secret (for secure login)');
    $this->make('TEST', 'Please enter an community name');
    $this->make('TEST', 'Please enter an community website url');
    $this->make('TEST', 'Should the event feature be enabled [true/false]');
    $this->make('TEST', 'Should the cinema feature be enabled [true/false]');
    $this->make('TEST', 'Please enter the OpenWeatherMap API key');
    $this->make('TEST', 'Please enter the OpenWeatherMap Cinema City Id');

    $this->comment("Finished with setup process!");
  }

  private function make(string $envKeyWord, string $question, $isAnswerBool = false) {
    $answer = null;

    while(true) {
      $answer = $this->ask($question);

      $this->comment('Your answer was: [' . $answer . ']');

      $check = $this->anticipate('Is this correct? [Y/n]', ['y', 'n']);
      if ($check == null) {
        break;
      } else {
        if ($check == 'y') {
          break;
        }
      }
    }

    if ($isAnswerBool) {
      if ($answer == 'true') {
        $answer = true;
      } else {
        $answer = false;
      }
    }

    $this->changeEnvironmentVariable($envKeyWord, $answer);
  }

  private function changeEnvironmentVariable($key, $value) {
    $path = base_path('.env');

    if (is_bool(env($key))) {
      $old = env($key) ? 'true' : 'false';
    } elseif (env($key) === null) {
      $old = 'null';
    } else {
      $old = '"'.env($key).'"';
    }

    if (is_bool($value)) {
      if ($value) {
        $value = 'true';
      } else {
        $value = 'false';
      }
    } elseif ($value === null) {
      $value = 'null';
    } else {
      $value = '"'.$value.'"';
    }

    if (file_exists($path)) {
      file_put_contents($path, str_replace("$key=" . $old, "$key=" . $value, file_get_contents($path)));
    }
  }
}