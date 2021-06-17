<?php

namespace App\Models\Events;

use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use App\Utils\ArrayHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property boolean $forEveryone
 * @property string $created_at
 * @property string $updated_at
 * @property EventDecision[] $eventDecisions
 * @property EventDate[] $eventDates
 * @property EventForGroup[] $eventsForGroups
 * @property EventForSubgroup[] $eventsForSubgroups
 * @property EventLinkedBroadcast[] $eventLinkedBroadcasts
 * @property EventUserVotedForDecision[] $eventsUsersVotedForDecision
 */
class Event extends Model {
  protected $table = 'events';

  protected $fillable = [
    'name',
    'description',
    'forEveryone',
    'created_at',
    'updated_at',];
  protected $hidden = ['forEveryone', 'eventDates', 'eventDecisions'];
  protected $with = ['eventDecisions', 'eventDates'];

  /**
   * @return HasMany
   */
  public function eventDecisions(): HasMany {
    return $this->hasMany(EventDecision::class, 'event_id');
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
    $groups = ArrayHelper::getPropertyArrayOfObjectArray($this->eventsForGroups(), 'group');
    usort($groups, static function ($a, $b) {
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
    $subgroups = ArrayHelper::getPropertyArrayOfObjectArray($this->eventsForSubgroups(), 'subgroup');
    usort($subgroups, static function ($a, $b) {
      return strcmp($a->orderN, $b->orderN);
    });

    return $subgroups;
  }

  /**
   * @return EventLinkedBroadcast[]
   */
  public function getLinkedBroadcasts(): array {
    return $this->hasMany(EventLinkedBroadcast::class)
      ->get()->all();
  }

  /**
   * @return HasMany
   */
  private function usersVotedForDecision(): HasMany {
    return $this->hasMany(EventUserVotedForDecision::class);
  }

  /**
   * @return EventUserVotedForDecision[]
   */
  public function getUsersVotedForDecision(): array {
    return $this->usersVotedForDecision()
      ->get()->all();
  }

  /**
   * @return HasMany
   */
  public function eventDates(): HasMany {
    return $this->hasMany(EventDate::class);
  }

  /**
   * @return EventDate|HasMany
   */
  public function getFirstEventDate(): EventDate | HasMany {
    return $this->eventDates()->oldest('date')->first();
  }

  /**
   * @return EventDate|HasMany
   */
  public function getLastEventDate(): EventDate | HasMany {
    return $this->eventDates()->latest('date')->first();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['for_everyone'] = $this->forEveryone;
    $returnable['decisions'] = $this->eventDecisions;
    $returnable['dates'] = $this->eventDates;
    $returnable['start_date'] = $this->getFirstEventDate()?->date;
    $returnable['end_date'] = $this->getLastEventDate()?->date;

    return $returnable;
  }

  /**
   * @param int $userId
   * @return array
   */
  public function toArrayWithUserDecisionByUserId(int $userId): array {
    $returnable = $this->toArray();
    $eventUserVotedFor = $this->usersVotedForDecision()->where('user_id', $userId)->first();
    $returnable['already_voted'] = ($eventUserVotedFor != null);
    $returnable['user_decision'] = $eventUserVotedFor;

    return $returnable;
  }
}
