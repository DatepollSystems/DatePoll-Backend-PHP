<?php namespace App\Console\Commands;

use App\Permissions;
use App\User;
use App\UserPermission;
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
      'join_date' => date('Y-m-d'),
      'email' => 'admin@inter.datepoll',
      'password' => app('hash')->make('123456'),
      'activated' => true,
      'rank' => 'admin',
      'force_password_change' => true,
      'streetname' => 'Alauntalstraße',
      'streetnumber' => '6-7',
      'zipcode' => '3500',
      'location' => 'Krems an der Donau',
      'activity' => 'active'
    ]);
    if($user->save()) {
      $this->comment(PHP_EOL."Added admin user".PHP_EOL);

      $permission = new UserPermission([
        'user_id' => $user->id,
        'permission' => 'root.administration'
      ]);

      if($permission->save()) {
        $this->comment(PHP_EOL."Added permissions".PHP_EOL);
      } else {
        $this->comment(PHP_EOL."Could not permissions".PHP_EOL);
      }

    } else {
      $this->comment(PHP_EOL."Could not add admin user".PHP_EOL);
    }
  }
}