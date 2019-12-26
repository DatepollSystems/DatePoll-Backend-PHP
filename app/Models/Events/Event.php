<?php

namespace App\Models\Events;

use App\Models\Groups\Group;
use App\Models\User\User;
use App\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use stdClass;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property boolean $forEveryone
 * @property string $created_at
 * @property string $updated_at
 * @property EventDecision[] $eventsDecisions
 * @property EventForGroup[] $eventsForGroups
 * @property EventForSubgroup[] $eventsForSubgroups
 * @property EventUserVotedForDecision[] $eventsUsersVotedForDecision
 */
class Event extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'events';

  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'description',
    'forEveryone',
    'created_at',
    'updated_at'];

  /**
   * @return Collection
   */
  public function eventsDecisions() {
    return $this->hasMany('App\Models\Events\EventDecision', 'event_id')
                ->get();
  }

  /**
   * @return Collection
   */
  public function eventsForGroups() {
    return $this->hasMany('App\Models\Events\EventForGroup')
                ->get();
  }

  /**
   * @return Collection
   */
  public function eventsForSubgroups() {
    return $this->hasMany('App\Models\Events\EventForSubgroup')
                ->get();
  }

  /**
   * @return Collection
   */
  public function usersVotedForDecision() {
    return $this->hasMany('App\Models\Events\EventUserVotedForDecision')
                ->get();
  }

  /**
   * @return Collection
   */
  public function getEventDates() {
    return $this->hasMany('App\Models\Events\EventDate')
                ->get();
  }

  /**
   * @return stdClass
   */
  public function getReturnable() {
    $returnable = new stdClass();

    $returnable->id = $this->id;
    $returnable->name = $this->name;
    $returnable->description = $this->description;
    $returnable->start_date = $this->startDate;
    $returnable->end_date = $this->endDate;
    $returnable->for_everyone = $this->forEveryone;
    $returnable->location = $this->location;

    $decisions = array();
    foreach ($this->eventsDecisions() as $eventsDecision) {
      $decision = new stdClass();
      $decision->id = $eventsDecision->id;
      $decision->decision = $eventsDecision->decision;
      $decision->event_id = $eventsDecision->event_id;
      $decision->show_in_calendar = $eventsDecision->showInCalendar;

      $decisions[] = $decision;
    }

    $returnable->decisions = $decisions;

    return $returnable;
  }

  /**
   * @param User $user
   * @return stdClass
   */
  public function getResults($user) {
    $results = new stdClass();

    $anonymous = $user->hasPermission(Permissions::$ROOT_ADMINISTRATION) || $user->hasPermission(Permissions::$EVENTS_ADMINISTRATION) || $user->hasPermission(Permissions::$EVENTS_VIEW_DETAILS) ? false : true;

    if ($this->forEveryone) {
      $groups = array();

      foreach (Group::all() as $group) {
        $groupToSave = new stdClass();

        $groupToSave->id = $group->id;
        $groupToSave->name = $group->name;

        $usersMemberOfGroup = array();
        foreach ($group->usersMemberOfGroups() as $userMemberOfGroup) {
          $usersMemberOfGroup[] = $this->getDecision($userMemberOfGroup->user(), $anonymous);
        }
        $groupToSave->users = $usersMemberOfGroup;

        $subgroups = array();
        foreach ($group->subgroups() as $subgroup) {
          $subgroupToSave = new stdClass();

          $subgroupToSave->id = $subgroup->id;
          $subgroupToSave->name = $subgroup->name;

          $usersMemberOfSubgroup = array();
          foreach ($subgroup->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
            $usersMemberOfSubgroup[] = $this->getDecision($userMemberOfSubgroup->user(), $anonymous);
          }

          $subgroupToSave->users = $usersMemberOfSubgroup;

          $subgroups[] = $subgroupToSave;
        }
        $groupToSave->subgroups = $subgroups;
        $groups[] = $groupToSave;
      }

      $results->groups = $groups;

      $all = array();
      foreach (User::all() as $user) {
        $all[] = $this->getDecision($user, $anonymous);
      }

      $results->allUsers = $all;
    } else {
      $all = array();
      $allSubgroups = array();

      $groups = array();

      $foreachGroups = array();
      foreach ($this->eventsForGroups() as $eventForGroup) {
        $foreachGroups[] = $eventForGroup->group();
      }

      foreach ($foreachGroups as $group) {
        $groupToSave = new stdClass();

        $groupToSave->id = $group->id;
        $groupToSave->name = $group->name;

        $usersMemberOfGroup = array();
        foreach ($group->usersMemberOfGroups() as $userMemberOfGroup) {
          $user = $this->getDecision($userMemberOfGroup->user(), $anonymous);
          $usersMemberOfGroup[] = $user;
          if (!in_array($user, $all)) {
            $all[] = $user;
          }
        }
        $groupToSave->users = $usersMemberOfGroup;

        $subgroups = array();
        foreach ($group->subgroups() as $subgroup) {
          $subgroupToSave = new stdClass();

          $subgroupToSave->id = $subgroup->id;
          $subgroupToSave->name = $subgroup->name;
          $subgroupToSave->parent_group_name = $subgroup->group()->name;
          $subgroupToSave->parent_group_id = $subgroup->group_id;

          $usersMemberOfSubgroup = array();
          foreach ($subgroup->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
            $user = $this->getDecision($userMemberOfSubgroup->user(), $anonymous);
            $usersMemberOfSubgroup[] = $user;
            if (!in_array($user, $all)) {
              $all[] = $user;
            }
          }

          $subgroupToSave->users = $usersMemberOfSubgroup;

          $subgroups[] = $subgroupToSave;
          $allSubgroups[] = $subgroupToSave;
        }
        $groupToSave->subgroups = $subgroups;
        $groups[] = $groupToSave;
      }

      $unknownGroupToSave = new stdClass();

      $unknownGroupToSave->id = -1;
      $unknownGroupToSave->name = "unknown";
      $unknownGroupToSave->users = array();

      $subgroups = array();
      foreach ($this->eventsForSubgroups() as $eventForSubgroup) {
        $subgroupToSave = new stdClass();

        $subgroup = $eventForSubgroup->subgroup();
        $subgroupToSave->id = $subgroup->id;
        $subgroupToSave->name = $subgroup->name;
        $subgroupToSave->parent_group_name = $subgroup->group()->name;
        $subgroupToSave->parent_group_id = $subgroup->group_id;

        $usersMemberOfSubgroup = array();
        foreach ($subgroup->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
          $user = $this->getDecision($userMemberOfSubgroup->user(), $anonymous);
          $usersMemberOfSubgroup[] = $user;
          if (!in_array($user, $all)) {
            $all[] = $user;
          }
        }

        $subgroupToSave->users = $usersMemberOfSubgroup;

        if (!in_array($subgroupToSave, $allSubgroups)) {
          $subgroups[] = $subgroupToSave;
        }
      }
      $unknownGroupToSave->subgroups = $subgroups;
      $groups[] = $unknownGroupToSave;

      $results->groups = $groups;

      $results->allUsers = $all;
    }
    $results->anonymous = $anonymous;
    return $results;
  }

  /**
   * @param $user
   * @param bool $anonymous
   * @return stdClass
   */
  private function getDecision($user, bool $anonymous) {
    $userToSave = new stdClass();
    if (!$anonymous) {
      $userToSave->id = $user->id;
      $userToSave->firstname = $user->firstname;
      $userToSave->surname = $user->surname;
    } else {
      $userToSave->id = null;
      $userToSave->firstname = null;
      $userToSave->surname = null;
    }

    $decision = EventUserVotedForDecision::where('user_id', $user->id)
                                         ->where('event_id', $this->id)
                                         ->first();
    $userToSave->additional_information = null;
    if ($decision == null) {
      $userToSave->decisionId = null;
      $userToSave->decision = null;
    } else {
      $userToSave->decisionId = $decision->decision()->id;
      $userToSave->decision = $decision->decision()->decision;
      if (!$anonymous) {
        $userToSave->additional_information = $decision->additionalInformation;
      }
    }

    return $userToSave;
  }
}
