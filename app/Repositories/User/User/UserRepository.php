<?php

namespace App\Repositories\User\User;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\ActivateUser;
use App\Models\User\User;
use App\Models\User\UserCode;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserPermission;
use App\Models\User\UserTelephoneNumber;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Exception;
use Illuminate\Support\Facades\Hash;
use stdClass;

class UserRepository implements IUserRepository {
  protected ISettingRepository $settingRepository;
  protected IUserSettingRepository $userSettingRepository;
  protected IUserChangeRepository $userChangeRepository;
  protected IEventRepository $eventRepository;
  protected IBroadcastRepository $broadcastRepository;

  public function __construct(
    ISettingRepository $settingRepository,
    IUserSettingRepository $userSettingRepository,
    IEventRepository $eventRepository,
    IBroadcastRepository $broadcastRepository,
    IUserChangeRepository $userChangeRepository
  ) {
    $this->settingRepository = $settingRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->eventRepository = $eventRepository;
    $this->broadcastRepository = $broadcastRepository;
    $this->userChangeRepository = $userChangeRepository;
  }

  /**
   * @return User[]
   */
  public function getAllUsers(): array {
    return User::all()->all();
  }

  /**
   * @return User[]
   */
  public function getAllUsersOrderedBySurname() {
    return User::orderBy('surname')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return User|null
   */
  public function getUserById(int $id): ?User {
    return User::find($id);
  }

  /**
   * @param string $username
   * @return User|null
   */
  public function getUserByUsername(string $username): ?User {
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
   * @param string|null $memberNumber
   * @param string|null $internalComment
   * @param bool $informationDenied
   * @param string|null $bvMember
   * @param int $editorId
   * @param User|null $user
   * @return User|null
   * @throws Exception
   */
  public function createOrUpdateUser(
    ?string $title,
    string $username,
    string $firstname,
    string $surname,
    string $birthday,
    string $joinDate,
    string $streetname,
    string $streetnumber,
    int $zipcode,
    string $location,
    bool $activated,
    string $activity,
    array $phoneNumbers,
    array $emailAddresses,
    ?string $memberNumber,
    ?string $internalComment,
    ?bool $informationDenied,
    ?string $bvMember,
    int $editorId,
    User $user = null
  ): ?User {
    if ($bvMember == null) {
      $bvMember = '';
    }

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
        'member_number' => $memberNumber,
        'internal_comment' => $internalComment,
        'bv_member' => $bvMember,
        'password' => 'Null', ]);

      if (! $user->save()) {
        Logging::error('createOrUpdateUser', 'Could not save user into database!');

        return null;
      }
    } else {
      $this->userChangeRepository->checkForPropertyChange('username', $user->id, $editorId, $username, $user->username);
      $this->userChangeRepository->checkForPropertyChange('title', $user->id, $editorId, $title, $user->title);
      $this->userChangeRepository->checkForPropertyChange('firstname', $user->id, $editorId, $firstname, $user->firstname);
      $this->userChangeRepository->checkForPropertyChange('surname', $user->id, $editorId, $surname, $user->surname);
      $this->userChangeRepository->checkForPropertyChange('birthday', $user->id, $editorId, $birthday, $user->birthday);
      $this->userChangeRepository->checkForPropertyChange('join_date', $user->id, $editorId, $joinDate, $user->join_date);
      $this->userChangeRepository->checkForPropertyChange('streetname', $user->id, $editorId, $streetname, $user->streetname);
      $this->userChangeRepository->checkForPropertyChange('streetnumber', $user->id, $editorId, $streetnumber, $user->streetnumber);
      $this->userChangeRepository->checkForPropertyChange('location', $user->id, $editorId, $location, $user->location);
      $this->userChangeRepository->checkForPropertyChange('activity', $user->id, $editorId, $activity, $user->activity);
      $this->userChangeRepository->checkForPropertyChange('member_number', $user->id, $editorId, $memberNumber, $user->member_number);
      $this->userChangeRepository->checkForPropertyChange(
        'internal_comment',
        $user->id,
        $editorId,
        $internalComment,
        $user->internal_comment
      );
      $this->userChangeRepository->checkForPropertyChange('bv_member', $user->id, $editorId, $bvMember, $user->bv_member);
      // Don't use checkForPropertyChange function because these values aren't strings
      if ($user->zipcode != $zipcode) {
        $this->userChangeRepository->createUserChange('zipcode', $user->id, $editorId, $zipcode, $user->zipcode);
      }
      if ($user->activated != $activated) {
        $this->userChangeRepository->createUserChange('activated', $user->id, $editorId, $activated, $user->activated);
      }
      if ($user->information_denied != $informationDenied) {
        $this->userChangeRepository->createUserChange(
          'informationDenied',
          $user->id,
          $editorId,
          $informationDenied,
          $user->information_denied
        );
      }

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
      $user->member_number = $memberNumber;
      $user->internal_comment = $internalComment;
      $user->bv_member = $bvMember;
    }
    if ($informationDenied == null) {
      $user->information_denied = false;
    } else {
      $user->information_denied = $informationDenied;
    }

    if (! $user->save()) {
      Logging::error('createUser', 'Could not save user into database!');

      return null;
    }

    // Email addresses manager only deletes changed email addresses
    if ($this->updateUserEmailAddresses($user, $emailAddresses, $editorId) == null) {
      return null;
    }

    //----Phone numbers manager only deletes changed phone numbers---
    $phoneNumbersWhichHaveNotBeenDeleted = [];

    foreach ($user->telephoneNumbers() as $oldPhoneNumber) {
      $toDelete = true;

      foreach ($phoneNumbers as $phoneNumber) {
        if ($oldPhoneNumber['label'] == $phoneNumber['label'] and $oldPhoneNumber['number'] == $phoneNumber['number']) {
          $toDelete = false;
          $phoneNumbersWhichHaveNotBeenDeleted[] = $phoneNumber;
          break;
        }
      }

      if ($toDelete) {
        $phoneNumberToDeleteObject = UserTelephoneNumber::find($oldPhoneNumber->id);
        $this->userChangeRepository->createUserChange('phone number', $user->id, $editorId, null, $phoneNumberToDeleteObject->number);
        if (! $phoneNumberToDeleteObject->delete()) {
          Logging::error('createOrUpdateUser', 'Could not delete $phoneNumberToDeleteObject');

          return null;
        }
      }
    }

    foreach ($phoneNumbers as $phoneNumber) {
      $toAdd = true;

      foreach ($phoneNumbersWhichHaveNotBeenDeleted as $phoneNumberWhichHasNotBeenDeleted) {
        if ($phoneNumber['label'] == $phoneNumberWhichHasNotBeenDeleted['label'] and $phoneNumber['number'] == $phoneNumberWhichHasNotBeenDeleted['number']) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $phoneNumberToSave = new UserTelephoneNumber([
          'label' => $phoneNumber['label'],
          'number' => $phoneNumber['number'],
          'user_id' => $user->id, ]);

        if (! $phoneNumberToSave->save()) {
          Logging::error('createOrUpdateUser', 'Could not save phoneNumberToSave');

          return null;
        }
        $this->userChangeRepository->createUserChange('phone number', $user->id, $editorId, $phoneNumber['number'], null);
      }
    }

    Logging::info('createOrUpdateUser', 'Successfully created or updated user ' . $user->id);

    return $user;
  }

  /**
   * @param User $user
   * @param string[] $emailAddresses
   * @param int $editorId
   * @return bool
   * @throws Exception
   */
  public function updateUserEmailAddresses(User $user, array $emailAddresses, int $editorId): bool {
    $emailAddressesWhichHaveNotBeenDeleted = [];

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
        $this->userChangeRepository->createUserChange('email address', $user->id, $editorId, null, $oldEmailAddress->email);
        if (! $oldEmailAddress->delete()) {
          Logging::error('updateUserEmailAddresses', 'Could not delete emailAddressToDeleteObject');

          return false;
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
          'user_id' => $user->id, ]);

        if (! $emailAddressToSave->save()) {
          Logging::error('updateUserEmailAddresses', 'Could not save $emailAddressToSave');

          return false;
        }
        $this->userChangeRepository->createUserChange('email address', $user->id, $editorId, $emailAddress, null);
      }
    }

    return true;
  }

  /**
   * @param string[]|array $permissions
   * @param User $user
   * @return bool
   */
  public function createOrUpdatePermissionsForUser(array $permissions, User $user): bool {
    $permissionsWhichHaveNotBeenDeleted = [];

    foreach ($user->getPermissions() as $oldPermission) {
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
        if (! $permissionToDeleteObject->delete()) {
          Logging::error(
            'createOrUpdatePermissionsForUser',
            'Could not delete old permission: ' . $permissionToDeleteObject->permission . ' for user: ' . $user->id
          );

          return false;
        }
      }
    }

    foreach ($permissions as $permission) {
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
          'user_id' => $user->id, ]);
        if (! $permissionToSave->save()) {
          Logging::error(
            'createOrUpdatePermissionsForUser',
            'Could not add permission: ' . $permission . ' for user: ' . $user->id
          );

          return false;
        }
      }
    }

    return true;
  }

  /**
   * @param User $user
   */
  public function activateUser(User $user): void {
    $randomPassword = UserCode::generateCode();
    $user->password = Hash::make($randomPassword . $user->id);
    ;
    $user->force_password_change = true;
    $user->activated = true;
    $user->save();

    dispatch(new SendEmailJob(new ActivateUser(
      $user->getCompleteName(),
      $user->username,
      $randomPassword,
      $this->settingRepository
    ), $user->getEmailAddresses()))->onQueue('low');
  }

  /**
   * @return array
   */
  public function exportAllUsers(): array {
    $toReturnUsers = [];

    $users = $this->getAllUsersOrderedBySurname();
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
      $toReturnUser->MitgliedsNummer = $user->member_number;

      $telephoneNumbers = '';
      foreach ($user->telephoneNumbers() as $telephoneNumber) {
        $telephoneNumbers .= $telephoneNumber->number . ', ';
      }

      $toReturnUser->Telefonnummern = $telephoneNumbers;

      $groups = '';
      foreach ($user->usersMemberOfGroups() as $usersMemberOfGroup) {
        $role = '';
        if ($usersMemberOfGroup->role != null) {
          if (strlen($usersMemberOfGroup->role) != 0) {
            $role .= ' - ' . $usersMemberOfGroup->role;
          }
        }

        $groups .= $usersMemberOfGroup->group()->name . $role . ', ';
      }
      $toReturnUser->Gruppen = $groups;

      $subgroups = '';
      foreach ($user->usersMemberOfSubgroups() as $usersMemberOfSubgroup) {
        $role = '';
        if ($usersMemberOfSubgroup->role != null) {
          if (strlen($usersMemberOfSubgroup->role) != 0) {
            $role .= ' - ' . $usersMemberOfSubgroup->role;
          }
        }

        $subgroups .= '[' . $usersMemberOfSubgroup->subgroup()
          ->group()->name . '] ' . $usersMemberOfSubgroup->subgroup()->name . $role . ', ';
      }
      $toReturnUser->Register = $subgroups;

      $performanceBadgeForUser = '';
      foreach ($user->getPerformanceBadges() as $performanceBadge) {
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
   * @return User[]
   */
  public function getAllNotActivatedUsers(): array {
    return User::where('activated', 0)
      ->get()->all();
  }

  /**
   * @param User $user
   * @param string $password
   * @return bool
   */
  public function changePasswordOfUser(User $user, string $password): bool {
    $user->password = Hash::make($password . $user->id);

    return $user->save();
  }

  /**
   * @param User $user
   * @param string $password
   * @return bool
   */
  public function checkPasswordOfUser(User $user, string $password): bool {
    if (Hash::needsRehash($user->password)) {
      if (! $this->changePasswordOfUser($user, $password)) {
        Logging::error('checkPasswordOfUser', 'Password need rehash. Could not change password.');

        return false;
      }
    }

    return Hash::check($password . $user->id, $user->password);
  }

  /**
   * @param User $user
   * @return array
   */
  public function getHomepageDataForUser(User $user): array {
    $bookingsToShow = [];
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

    $eventsToShow = [];
    if ($this->settingRepository->getEventsEnabled()) {
      $eventsToShow = $this->eventRepository->getOpenEventsForUser($user);
    }

    $broadcastsToShow = [];
    if ($this->settingRepository->getBroadcastsEnabled()) {
      $broadcasts = $this->broadcastRepository->getBroadcastsForUserByIdOrderedByDate($user->id, 3);
      foreach ($broadcasts as $broadcast) {
        $broadcastsToShow[] = $this->broadcastRepository->getBroadcastReturnable($broadcast);
      }
    }

    $users = $this->getAllUsers();
    $birthdaysToShow = [];
    foreach ($users as $user) {
      if ($this->userSettingRepository->getShareBirthdayForUser($user)) {
        $addTimeDate = date('m-d', strtotime('+15 days', strtotime(date('Y-m-d'))));
        $remTimeDate = date('m-d', strtotime('-1 days', strtotime(date('Y-m-d'))));
        if ($remTimeDate < date('m-d', strtotime($user->birthday)) && date(
          'm-d',
          strtotime($user->birthday)
        ) < $addTimeDate) {
          $birthdayToShow = new stdClass();

          $birthdayToShow->name = $user->getCompleteName();
          $birthdayToShow->date = $user->birthday;

          $birthdaysToShow[] = $birthdayToShow;
        }
      }
    }

    usort($birthdaysToShow, function ($a, $b) {
      return strcmp(date('m-d', strtotime($a->date)), date('m-d', strtotime($b->date)));
    });

    return [
      'msg' => 'List of your bookings, events, broadcasts and birthdays in the next month',
      'events' => $eventsToShow,
      'bookings' => $bookingsToShow,
      'broadcasts' => $broadcastsToShow,
      'birthdays' => $birthdaysToShow, ];
  }
}
