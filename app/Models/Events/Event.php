<?php

namespace App\Models\Events;

use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use Illuminate\Database\Eloquent\Model;

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
class Event extends Model {
  protected $table = 'events';

  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'description',
    'forEveryone',
    'created_at',
    'updated_at', ];

  /**
   * @return EventDecision[]
   */
  public function eventsDecisions(): array {
    return $this->hasMany(EventDecision::class, 'event_id')
      ->get()->all();
  }

  /**
   * @return EventForGroup[]
   */
  public function eventsForGroups(): array {
    return $this->hasMany(EventForGroup::class)
      ->get()->all();
  }

  /**
   * @return Group[]
   */
  public function getGroupsOrdered(): array {
    $groups = [];
    foreach ($this->eventsForGroups() as $eventForGroup) {
      $groups[] = $eventForGroup->group();
    }
    usort($groups, function ($a, $b) {
      return strcmp($a->orderN, $b->orderN);
    });

    return $groups;
  }

  /**
   * @return EventForSubgroup[]
   */
  public function eventsForSubgroups(): array {
    return $this->hasMany(EventForSubgroup::class)
      ->get()->all();
  }

  /**
   * @return Subgroup[]
   */
  public function getSubgroupsOrdered(): array {
    $subgroups = [];
    foreach ($this->eventsForSubgroups() as $eventForSubgroup) {
      $subgroups[] = $eventForSubgroup->subgroup();
    }
    usort($subgroups, function ($a, $b) {
      return strcmp($a->orderN, $b->orderN);
    });

    return $subgroups;
  }

  /**
   * @return EventUserVotedForDecision[]
   */
  public function usersVotedForDecision(): array {
    return $this->hasMany(EventUserVotedForDecision::class)
      ->get()->all();
  }

  /**
   * @return EventDate[]
   */
  public function getEventDates(): array {
    return $this->hasMany(EventDate::class, 'event_id')
      ->get()->all();
  }
}
