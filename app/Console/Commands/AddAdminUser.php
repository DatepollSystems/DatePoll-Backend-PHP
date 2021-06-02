<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserPermission;
use App\Permissions;
use App\Utils\Generator;
use Illuminate\Console\Command;

class AddAdminUser extends Command {
  protected $signature = 'add-admin-user';
  protected $description = 'Creates admin user with all permissions.';

  /**
   * @return void
   */
  public function handle(): void {
    if (! $this->confirm('Confirm creating an admin account', true)) {
      $this->comment('Aborting...');

      return;
    }
    $this->comment('Creating admin user...');

    $randomPassword = Generator::getRandom6DigitNumber();
    $username = 'admin-' . Generator::getRandomMixedNumberAndABCToken(2);

    $user = new User([
      'firstname' => 'Helmi',
      'surname' => 'GIS',
      'birthday' => date('Y-m-d'),
      'join_date' => date('Y-m-d'),
      'username' => $username,
      'password' => app('hash')->make($randomPassword),
      'activated' => true,
      'force_password_change' => true,
      'streetname' => 'Alauntalstraße',
      'streetnumber' => '6-7',
      'zipcode' => '3500',
      'location' => 'Krems an der Donau',
      'activity' => 'active',
      'bv_member' => 'bv_member',
    ]);
    if (! $user->save()) {
      $this->warn('Error during admin user creation.');

      return;
    }

    $this->comment('Setting password...');
    $user->password = app('hash')->make($randomPassword . $user->id);
    $user->save();

    $this->comment('Setting permissions...');
    $permission = new UserPermission([
      'user_id' => $user->id,
      'permission' => Permissions::$ROOT_ADMINISTRATION,
    ]);

    if (! $permission->save()) {
      $this->warn('Error during permission setting');
      $user->delete();

      return;
    }

    $this->info('Admin user created!');
    $this->info('Username: "' . $username . '"');
    $this->info('Password: "' . $randomPassword . '"');
  }
}
