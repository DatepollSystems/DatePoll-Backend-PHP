<?php namespace App\Console\Commands;

use App\User;
use Schema;
use Illuminate\Console\Command;

class AddAdminUser extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'addadminuser';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Drops all tables';

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
    $user = new User([
      'firstname' => 'Helmi',
      'surname' => 'GIS',
      'birthday' => date('Y-m-d'),
      'email' => 'admin@inter.datepoll',
      'password' => app('hash')->make('123456'),
      'rank' => 'admin',
      'force_password_change' => true,
      'streetname' => 'Alauntalstraße',
      'streetnumber' => '6-7',
      'zipcode' => '3500',
      'location' => 'Krems an der Donau'
    ]);
    if($user->save()) {
      $this->comment(PHP_EOL."Added admin user".PHP_EOL);
    } else {
      $this->comment(PHP_EOL."Could not add admin user".PHP_EOL);
    }
  }
}