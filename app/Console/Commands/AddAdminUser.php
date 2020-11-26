<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserPermission;
use Exception;
use Illuminate\Console\Command;

class AddAdminUser extends Command {
  protected $signature = 'add-admin-user';
  protected $description = 'Creates admin user with all permissions.';
  public function __construct() {
    parent::__construct();
  }

  /**
   * @return void
   * @throws Exception
   */
  public function handle() {
    if (! $this->confirm('Are you sure you want to create an admin account?', true)) {
      $this->comment('Aborting...');

      return;
    }
    $this->line('> Creating admin user...');
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
      'bv_member' => 'bv_member',
    ]);
    if ($user->save()) {
      $this->comment('Admin user created');

      $this->line('> Setting password...');
      $user->password = app('hash')->make('123456' . $user->id);
      $user->save();
      $this->comment('Password changed');

      $this->line('> Setting permissions...');
      $permission = new UserPermission([
        'user_id' => $user->id,
        'permission' => 'root.administration',
      ]);

      if ($permission->save()) {
        $this->comment('Permission set');
      } else {
        $this->warn('Error during permission setting');
        $user->delete();

        return;
      }

      $this->info('Admin user successful created');
      $this->info('Username: admin');
      $this->info('Password: 123456');
    } else {
      $this->warn('Error during admin user creation.');
    }
  }
}
