<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserPermission;
use App\Repositories\User\UserToken\IUserTokenRepository;
use Illuminate\Console\Command;

class SetupDatePoll extends ACommand
{
  protected $userTokenRepository = null;

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
   * @param IUserTokenRepository $userTokenRepository
   */
  public function __construct(IUserTokenRepository $userTokenRepository)
  {
    parent::__construct();

    $this->userTokenRepository = $userTokenRepository;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $this->alert('Welcome to the DatePoll setup process!');
    $this->line('Lets start with the mail connection.');

    $this->make('MAIL_DRIVER', "Please enter the mail driver", "smtp");
    $this->make('MAIL_HOST', 'Please enter the mail host', "mail.example.at");
    $this->make('MAIL_PORT', 'Please enter the mail port', "587");
    $this->make('MAIL_ENCRYPTION', 'Please enter the mail encryption', "tls");
    $this->make('MAIL_USERNAME', 'Please enter the mail username', "datepoll@mail.example.at");
    $this->make('MAIL_PASSWORD', 'Please enter the mail password', "super_secret_password");
    $this->make('MAIL_FROM_ADDRESS', 'Please enter the mail from address', "datepoll@mail.example.at");
    $this->make('MAIL_REPLY_ADDRESS', 'Please enter the mail reply address', "datepoll@mail.example.at");
    $this->make('MAIL_FROM_NAME', 'Please enter the mail sender name', "DatePoll");

    $this->line('Now some security related things.');

    $this->make('APP_KEY', 'Please enter an app key (for the hash function)', $this->userTokenRepository->generateUniqueRandomToken(64));
    $this->make('JWT_SECRET', 'Please enter an jwt secret (for secure login)', $this->userTokenRepository->generateUniqueRandomToken(64));

    $this->comment("Finished with setting up the env file!");
  }

  private function make(string $envKeyWord, string $question, string $default) {
    $answer = $this->askStringQuestion($question, $default);

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