<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserPermission;
use Illuminate\Console\Command;

class AddAdminUser extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'add-admin-user';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates admin user with all permissions.';

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
      'username' => 'admin',
      'password' => app('hash')->make('123456'),
      'activated' => true,
      'rank' => 'admin',
      'force_password_change' => true,
      'streetname' => 'Alauntalstraße',
      'streetnumber' => '6-7',
      'zipcode' => '3500',
      'location' => 'Krems an der Donau',
      'activity' => 'active',
      'bv_member' => 'bv_member'
    ]);
    if($user->save()) {
      $this->comment(PHP_EOL."Add Admin User | Created".PHP_EOL);

      $user->password = app('hash')->make('123456' . $user->id);
      $user->save();
      $this->comment(PHP_EOL."Add Admin User | Added password ".PHP_EOL);

      $permission = new UserPermission([
        'user_id' => $user->id,
        'permission' => 'root.administration'
      ]);

      if($permission->save()) {
        $this->comment(PHP_EOL."Add Admin User | Added permissions".PHP_EOL);
      } else {
        $this->comment(PHP_EOL."Add Admin User | Could not add permissions".PHP_EOL);
      }

      $this->comment(PHP_EOL."Add Admin User | Now login in | Username: admin | Password: 123456".PHP_EOL);

    } else {
      $this->comment(PHP_EOL."Add Admin User | Error".PHP_EOL);
    }
  }
}