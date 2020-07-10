<?php


namespace App\Repositories\User\User;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\ActivateUser;
use App\Models\User\User;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserPermission;
use App\Models\User\UserTelephoneNumber;
use App\Models\User\UserCode;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use stdClass;

class UserRepository implements IUserRepository
{
  protected $settingRepository = null;
  protected $userSettingRepository = null;
  protected $eventRepository = null;

  public function __construct(ISettingRepository $settingRepository, IUserSettingRepository $userSettingRepository, IEventRepository $eventRepository) {
    $this->settingRepository = $settingRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->eventRepository = $eventRepository;
  }

  /**
   * @return User[]|Collection
   */
  public function getAllUsers() {
    return User::all();
  }

  /**
   * @param int $id
   * @return User|null
   */
  public function getUserById(int $id) {
    return User::find($id);
  }

  /**
   * @param string $username
   * @return User|null
   */
  public function getUserByUsername(string $username) {
    return User::where('username', $username)
               ->first();
  }

  /**
   * @param string|null $title
   * @param string $username
   * @param string $firstname
   * @param string $surname
   * @param string $birthday
   * @param string $joinDate
   * @param string $streetname
   * @param string $streetnumber
   * @param int $zipcode
   * @param string $location
   * @param bool $activated
   * @param string $activity
   * @param array $phoneNumbers
   * @param string[] $emailAddresses
   * @param User|null $user
   * @return User|null
   */
  public function createOrUpdateUser($title, $username, $firstname, $surname, $birthday, $joinDate, $streetname, $streetnumber, $zipcode, $location, $activated, $activity, $phoneNumbers, $emailAddresses, User $user = null) {
    if ($user == null) {
      $user = new User([
        'title' => $title,
        'username' => $username,
        'firstname' => $firstname,
        'surname' => $surname,
        'birthday' => $birthday,
        'join_date' => $joinDate,
        'streetname' => $streetname,
        'streetnumber' => $streetnumber,
        'zipcode' => $zipcode,
        'location' => $location,
        'activated' => $activated,
        'activity' => $activity,
        'password' => 'Null']);

      if (!$user->save()) {
        Logging::error('createUser', 'Could not save user into database!');
        return null;
      }
    } else {
      $user->username = $username;
      $user->title = $title;
      $user->firstname = $firstname;
      $user->surname = $surname;
      $user->birthday = $birthday;
      $user->join_date = $joinDate;
      $user->streetname = $streetname;
      $user->streetnumber = $streetnumber;
      $user->zipcode = $zipcode;
      $user->location = $location;
      $user->activated = $activated;
      $user->activity = $activity;
      $user->save();
    }

    // Email addresses manager only deletes changed email addresses
    if ($this->updateUserEmailAddresses($user, $emailAddresses) == null) {
      return null;
    }

    //----Phone numbers manager only deletes changed phone numbers---
    $phoneNumbersWhichHaveNotBeenDeleted = array();

    $OldPhoneNumbers = $user->telephoneNumbers();
    foreach ($OldPhoneNumbers as $oldPhoneNumber) {
      $toDelete = true;

      foreach ((array)$phoneNumbers as $phoneNumber) {
        if ($oldPhoneNumber['label'] == $phoneNumber['label'] AND $oldPhoneNumber['number'] == $phoneNumber['number']) {
          $toDelete = false;
          $phoneNumbersWhichHaveNotBeenDeleted[] = $phoneNumber;
          break;
        }
      }

      if ($toDelete) {
        $phoneNumberToDeleteObject = UserTelephoneNumber::find($oldPhoneNumber->id);
        if (!$phoneNumberToDeleteObject->delete()) {
          Logging::error('createOrUpdateUser', 'Could not delete $phoneNumberToDeleteObject');
          return null;
        }
      }
    }

    foreach ((array)$phoneNumbers as $phoneNumber) {
      $toAdd = true;

      foreach ($phoneNumbersWhichHaveNotBeenDeleted as $phoneNumberWhichHasNotBeenDeleted) {
        if ($phoneNumber['label'] == $phoneNumberWhichHasNotBeenDeleted['label'] AND $phoneNumber['number'] == $phoneNumberWhichHasNotBeenDeleted['number']) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $phoneNumberToSave = new UserTelephoneNumber([
          'label' => $phoneNumber['label'],
          'number' => $phoneNumber['number'],
          'user_id' => $user->id]);

        if (!$phoneNumberToSave->save()) {
          Logging::error('createOrUpdateUser', 'Could not save phoneNumberToSave');
          return null;
        }
      }
    }

    Logging::info('createOrUpdateUser', 'Successfully created or updated user ' . $user->id);
    return $user;
  }

  /**
   * @param User $user
   * @param string[] $emailAddresses
   * @return bool|null
   * @throws Exception
   */
  public function updateUserEmailAddresses(User $user, $emailAddresses) {
    $emailAddressesWhichHaveNotBeenDeleted = array();

    $OldEmailAddresses = $user->emailAddresses();
    foreach ($OldEmailAddresses as $oldEmailAddress) {
      $toDelete = true;

      foreach ((array)$emailAddresses as $emailAddress) {
        if ($oldEmailAddress['email'] == $emailAddress) {
          $toDelete = false;
          $emailAddressesWhichHaveNotBeenDeleted[] = $emailAddress;
          break;
        }
      }

      if ($toDelete) {
        if (!$oldEmailAddress->delete()) {
          Logging::error('updateUserEmailAddresses', 'Could not delete emailAddressToDeleteObject');
          return null;
        }
      }
    }

    foreach ((array)$emailAddresses as $emailAddress) {
      $toAdd = true;

      foreach ($emailAddressesWhichHaveNotBeenDeleted as $EmailAddressWhichHasNotBeenDeleted) {
        if ($emailAddress == $EmailAddressWhichHasNotBeenDeleted) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $emailAddressToSave = new UserEmailAddress([
          'email' => $emailAddress,
          'user_id' => $user->id]);

        if (!$emailAddressToSave->save()) {
          Logging::error('updateUserEmailAddresses', 'Could not save $emailAddressToSave');
          return null;
        }
      }
    }

    return true;
  }

  /**
   * @param array $permissions
   * @param User $user
   * @return bool
   */
  public function createOrUpdatePermissionsForUser($permissions, User $user) {
    $permissionsWhichHaveNotBeenDeleted = array();

    $OldPermissions = $user->permissions();
    foreach ($OldPermissions as $oldPermission) {
      $toDelete = true;

      foreach ((array)$permissions as $permission) {
        if ($oldPermission['permission'] == $permission) {
          $toDelete = false;
          $permissionsWhichHaveNotBeenDeleted[] = $permission;
          break;
        }
      }

      if ($toDelete) {
        $permissionToDeleteObject = UserPermission::find($oldPermission->id);
        if (!$permissionToDeleteObject->delete()) {
          Logging::error('createOrUpdatePermissionsForUser', 'Could not delete old permission: ' . $permissionToDeleteObject->permission . ' for user: ' . $user->id);
          return false;
        }
      }
    }

    if ($permissions == null) {
      return true;
    }
    foreach ((array)$permissions as $permission) {
      $toAdd = true;

      foreach ($permissionsWhichHaveNotBeenDeleted as $permissionWhichHaveNotBeenDeleted) {
        if ($permission == $permissionWhichHaveNotBeenDeleted) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $permissionToSave = new UserPermission([
          'permission' => $permission,
          'user_id' => $user->id]);
        if (!$permissionToSave->save()) {
          Logging::error('createOrUpdatePermissionsForUser', 'Could not add permission: ' . $permission . ' for user: ' . $user->id);
          return false;
        }
      }
    }

    return true;
  }

  /**
   * @param User $user
   */
  public function activateUser(User $user) {
    $randomPassword = UserCode::generateCode();
    $user->password = app('hash')->make($randomPassword . $user->id);;
    $user->force_password_change = true;
    $user->activated = true;
    $user->save();

    dispatch(new SendEmailJob(new ActivateUser($user->firstname . " " . $user->surname, $user->username, $randomPassword, $this->settingRepository), $user->getEmailAddresses()))->onQueue('default');
  }

  /**
   * @param User $user
   * @return bool|null
   */
  public function deleteUser(User $user) {
    try {
      return $user->delete();
    } catch (Exception $e) {
      Logging::error('deleteUser', 'Exception: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * @return array
   */
  public function exportAllUsers() {
    $toReturnUsers = array();

    $users = $this->getAllUsers();
    foreach ($users as $user) {

      $toReturnUser = new stdClass();

      $toReturnUser->Email = '';

      $emailAddresses = $user->emailAddresses();
      foreach ($emailAddresses as $emailAddress) {
        $toReturnUser->Email = $emailAddress['email'] . ';';
      }

      $toReturnUser->Titel = $user->title;
      $toReturnUser->Vorname = $user->firstname;
      $toReturnUser->Nachname = $user->surname;
      $toReturnUser->Geburtstag = $user->birthday;
      $toReturnUser->Beitrittsdatum = $user->join_date;
      $toReturnUser->Straßenname = $user->streetname;
      $toReturnUser->Hausnummer = $user->streetnumber;
      $toReturnUser->Postleitzahl = $user->zipcode;
      $toReturnUser->Ortsname = $user->location;
      $toReturnUser->Aktivitaet = $user->activity;

      $telephoneNumbers = '';
      foreach ($user->telephoneNumbers() as $telephoneNumber) {
        $telephoneNumbers .= $telephoneNumber->number . ', ';
      }

      $toReturnUser->Telefonnummern = $telephoneNumbers;

      $groups = '';
      foreach ($user->usersMemberOfGroups() as $usersMemberOfGroup) {
        $groups .= $usersMemberOfGroup->group()->name . ', ';
      }
      $toReturnUser->Gruppen = $groups;

      $subgroups = '';
      foreach ($user->usersMemberOfSubgroups() as $usersMemberOfSubgroup) {
        $subgroups .= $usersMemberOfSubgroup->subgroup()
                                            ->group()->name . ' - ' . $usersMemberOfSubgroup->subgroup()->name . ', ';
      }
      $toReturnUser->Register = $subgroups;

      $performanceBadgeForUser = '';
      foreach ($user->performanceBadges() as $performanceBadge) {
        $performanceBadgeForUser .= $performanceBadge->instrument()->name . ': ' . $performanceBadge->performanceBadge()->name;
        if ($performanceBadge->date != '1970-01-01') {
          $performanceBadgeForUser .= ' am ' . $performanceBadge->date;
        }
        if ($performanceBadge->grade != null) {
          $performanceBadgeForUser .= ' mit ' . $performanceBadge->grade . ' Erfolg';
        }
        $performanceBadgeForUser .= '; ';
      }
      $toReturnUser->Leistungsabzeichen = $performanceBadgeForUser;

      $toReturnUsers[] = $toReturnUser;
    }

    return $toReturnUsers;
  }

  /**
   * @return Collection<User>|null
   */
  public function getAllNotActivatedUsers() {
    return User::where('activated', 0)
               ->get();
  }

  /**
   * @param User $user
   * @param string $notHashedPassword
   * @return bool
   */
  public function changePasswordOfUser(User $user, string $notHashedPassword) {
    $user->password = app('hash')->make($notHashedPassword . $user->id);
    return $user->save();
  }

  /**
   * @param User $user
   * @param string $password
   * @return bool
   */
  public function checkPasswordOfUser(User $user, string $password) {
    return Hash::check($password . $user->id, $user->password);
  }

  /**
   * @param User $user
   * @return array
   */
  public function getHomepageDataForUser(User $user) {
    $bookingsToShow = array();
    if ($this->settingRepository->getCinemaEnabled()) {
      $bookings = $user->moviesBookings();
      foreach ($bookings as $booking) {
        $movie = $booking->movie();

        if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 05:00:00')) {
          $bookingToShow = new stdClass();
          $bookingToShow->movie_id = $movie->id;
          $bookingToShow->movie_name = $movie->name;
          $bookingToShow->movie_date = $movie->date;
          $bookingToShow->amount = $booking->amount;

          if ($movie->worker() == null) {
            $bookingToShow->worker_id = null;
            $bookingToShow->worker_name = null;
          } else {
            $bookingToShow->worker_id = $movie->worker()->id;
            $bookingToShow->worker_name = $movie->worker()->firstname . ' ' . $movie->worker()->surname;
          }

          if ($movie->emergencyWorker() == null) {
            $bookingToShow->emergency_worker_id = null;
            $bookingToShow->emergency_worker_name = null;
          } else {
            $bookingToShow->emergency_worker_id = $movie->emergencyWorker()->id;
            $bookingToShow->emergency_worker_name = $movie->emergencyWorker()->firstname . ' ' . $movie->emergencyWorker()->surname;
          }

          $bookingsToShow[] = $bookingToShow;
        }
      }
    }

    $eventsToShow = array();
    if ($this->settingRepository->getEventsEnabled()) {
      $eventsToShow = $this->eventRepository->getOpenEventsForUser($user);
    }

    $users = User::all();
    $birthdaysToShow = array();
    foreach ($users as $user) {
      if ($this->userSettingRepository->getShareBirthdayForUser($user)) {
        $d = date_parse_from_format("Y-m-d", $user->birthday);
        if ($d["month"] == date('n')) {
          $birthdayToShow = new stdClass();

          $birthdayToShow->name = $user->firstname . ' ' . $user->surname;
          $birthdayToShow->date = $user->birthday;

          $birthdaysToShow[] = $birthdayToShow;
        }
      }
    }

    usort($birthdaysToShow, function ($a, $b) {
      return strcmp($b->date ,$a->date);
    });

    usort($birthdaysToShow, function ($a, $b) {
      return strcmp($a->date, $b->date);
    });

    return [
      'msg' => 'List of your bookings, events and birthdays in the next month',
      'events' => $eventsToShow,
      'bookings' => $bookingsToShow,
      'birthdays' => $birthdaysToShow];
  }
}