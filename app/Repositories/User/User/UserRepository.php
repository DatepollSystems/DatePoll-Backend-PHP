<?php


namespace App\Repositories\User\User;

use App\Jobs\SendEmailQueue;
use App\Logging;
use App\Mail\ActivateUser;
use App\Models\User\User;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserPermission;
use App\Models\User\UserTelephoneNumber;
use App\Models\UserCode;
use App\Repositories\Setting\ISettingRepository;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use stdClass;

class UserRepository implements IUserRepository
{
  protected $settingRepository = null;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }


  public function getAllUsers() {
    return User::all();
  }

  public function getUserById(int $id) {
    return User::find($id);
  }

  public function getUserByUsername(string $username) {
    return User::where('username', $username)
               ->first();
  }

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

    //----Email addresses manager only deletes changed email addresses---
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
        $emailAddressToDeleteObject = UserEmailAddress::find($oldEmailAddress->id);
        if (!$emailAddressToDeleteObject->delete()) {
          Logging::error('createOrUpdateUser', 'Could not delete emailAddressToDeleteObject');
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
          Logging::error('createOrUpdateUser', 'Could not save $emailAddressToSave');
          return null;
        }
      }
    }
    //---------------------------------------------------------------
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

  public function activateUser(User $user) {
    $randomPassword = UserCode::generateCode();
    $user->password = app('hash')->make($randomPassword . $user->id);;
    $user->force_password_change = true;
    $user->save();

    dispatch(new SendEmailQueue(new ActivateUser($user->firstname . " " . $user->surname, $user->username, $randomPassword, $this->settingRepository), $user));
  }

  public function deleteUser(User $user) {
    try {
      return $user->delete();
    } catch (Exception $e) {
      Logging::error('deleteUser', 'Exception: ' . $e->getMessage());
      return false;
    }
  }

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
        $subgroups .= $usersMemberOfSubgroup->subgroup()->group()->name . ' - ' . $usersMemberOfSubgroup->subgroup()->name . ', ';
      }
      $toReturnUser->Register = $subgroups;

      $performanceBadgeForUser = '';
      foreach($user->performanceBadges() as $performanceBadge) {
        $performanceBadgeForUser .= $performanceBadge->instrument()->name . ': ' . $performanceBadge->performanceBadge()->name;
        if($performanceBadge->date != '1970-01-01') {
          $performanceBadgeForUser .= ' am ' . $performanceBadge->date;
        }
        if($performanceBadge->grade != null) {
          $performanceBadgeForUser .= ' mit ' . $performanceBadge->grade . ' Erfolg';
        }
        $performanceBadgeForUser .= '; ';
      }
      $toReturnUser->Leistungsabzeichen = $performanceBadgeForUser;

      $toReturnUsers[] = $toReturnUser;
    }

    return $toReturnUsers;
  }

  public function getAllNotActivatedUsers() {
    return User::where('activated', 0)->get();
  }

  public function changePasswordOfUser(User $user, string $notHashedPassword) {
    $user->password = app('hash')->make($notHashedPassword . $user->id);
    return $user->save();
  }

  public function checkPasswordOfUser(User $user, string $password)  {
    return Hash::check($password . $user->id, $user->password);
  }
}