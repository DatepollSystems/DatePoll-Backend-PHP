<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserPermission;
use App\Models\User\UserTelephoneNumber;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Console\Command;
use SimpleExcel\SimpleExcel;

class MigrateMembers extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'migrate-members';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Migrates members.';

  protected $userRepository = null;

  /**
   * Create a new command instance.
   *
   * @param IUserRepository $userRepository
   */
  public function __construct(IUserRepository $userRepository)
  {
    parent::__construct();

    $this->userRepository = $userRepository;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $excel = new SimpleExcel('csv');                    // instantiate new object (will automatically construct the parser & writer type as XML)

    $excel->parser->loadFile(__DIR__ . '/members.csv');            // load an XML file from server to be parsed

    $foo = $excel->parser->getField();                  // get complete array of the table

    $users = array();
    foreach ($foo as $row) {
      $user = new \stdClass();
      $user->title = $row[0];
      $user->username  = strtolower(substr($row[2], 0, 1) . '.' . $row[3]);
      $user->firstname = $row[2];
      $user->surname = $row[3];
      $user->join_date = $row[11] . '-01-01';
      $user->streetname = $row[4];
      $user->streetnumber = '100000';
      $user->zipcode = '1111';
      $user->location = $row[5];
      $user->activated = false;
      $user->activity = $row[12];
      $user->password = 'Null';
      $user->email_address = $row[6];
      $user->telephone_number = $row[9];

      if (strpos(substr($row[10], 6, 1), "0") !== false || strpos(substr($row[10], 6, 1), "1") !== false) {
        $birthday = '20' . substr($row[10], 6, 2) . '-' . substr($row[10], 3, 2) . '-' . substr($row[10], 0, 2);
      } else {
        $birthday = '19' . substr($row[10], 6, 2) . '-' . substr($row[10], 3, 2) . '-' . substr($row[10], 0, 2);
      }
      $user->birthday = $birthday;

      foreach ($this->userRepository->getAllUsers() as $existingUser) {
        if (strpos($existingUser->firstname, $user->firstname) !== false && strpos($existingUser->surname, $user->surname) !== false) {
          $this->comment('Already existing...');
          break;
        }

        $users[] = $user;
      }
    }

    $this->comment('Creating users...');

    foreach ($users as $user) {
      $createUser = new User([
        'title' => $user->title,
        'username' => $user->username,
        'firstname' => $user->firstname,
        'surname' => $user->surname,
        'birthday' => $user->birthday,
        'join_date' => $user->join_date,
        'streetname' => $user->streetname,
        'streetnumber' => $user->streetnumber,
        'zipcode' => $user->zipcode,
        'location' => $user->location,
        'activated' => $user->activated,
        'activity' => $user->activity,
        'password' => 'Null']);

      try {
        $createUser->save();

        if ($user->telephone_number != null) {
          if (strlen($user->telephone_number) > 0) {
            $phoneNumberToSave = new UserTelephoneNumber([
              'label' => 'Handy',
              'number' => $user->telephone_number,
              'user_id' => $createUser->id]);
            $phoneNumberToSave->save();
          }
        }

        $emailAddressToSave = new UserEmailAddress([
          'email' => $user->email_address,
          'user_id' => $createUser->id]);
        $emailAddressToSave->save();

      } catch (\Exception $exception) {
        $this->comment($exception->getMessage());
      }

    }

    $this->comment('Done.');

  }
}