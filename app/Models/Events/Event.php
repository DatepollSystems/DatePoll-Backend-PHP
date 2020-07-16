<?php

namespace App\Models\Events;

use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
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
 * @property EventDate[] $eventDates
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
   * @return Collection | EventDecision[] | null
   */
  public function eventsDecisions() {
    return $this->hasMany('App\Models\Events\EventDecision', 'event_id')
                ->get();
  }

  /**
   * @return Collection | EventForGroup[] | null
   */
  public function eventsForGroups() {
    return $this->hasMany('App\Models\Events\EventForGroup')
                ->get();
  }

  /**
   * @return Subgroup[] | null
   */
  public function getGroupsOrderedByName() {
    $eventForGroups = $this->eventsForGroups();
    $groups = array();
    foreach ($eventForGroups as $eventForGroup) {
      $groups[] = $eventForGroup->group();
    }
    usort($groups, function ($a, $b) {
      return strcmp($a->name, $b->name);
    });
    return $groups;
  }

  /**
   * @return Collection | EventForSubgroup[] | null
   */
  public function eventsForSubgroups() {
    return $this->hasMany('App\Models\Events\EventForSubgroup')
                ->get();
  }

  /**
   * @return Subgroup[] | null
   */
  public function getSubgroupsOrderedByName() {
    $eventForSubgroups = $this->eventsForSubgroups();
    $subgroups = array();
    foreach ($eventForSubgroups as $eventForSubgroup) {
      $subgroups[] = $eventForSubgroup->subgroup();
    }
    usort($subgroups, function ($a, $b) {
      return strcmp($a->name, $b->name);
    });
    return $subgroups;
  }

  /**
   * @return Collection | EventUserVotedForDecision[] | null
   */
  public function usersVotedForDecision() {
    return $this->hasMany('App\Models\Events\EventUserVotedForDecision')
                ->get();
  }

  /**
   * @return Collection | EventDate[] | null
   */
  public function getEventDates() {
    return $this->hasMany('App\Models\Events\EventDate', 'event_id')
                ->get();
  }
}
