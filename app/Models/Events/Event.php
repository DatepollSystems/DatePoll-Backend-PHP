<?php

namespace App\Models\Events;

use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

  protected $hidden = ['forEveryone'];

  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'description',
    'forEveryone',
    'created_at',
    'updated_at',];

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


  private function eventDates(): HasMany {
    return $this->hasMany(EventDate::class);
  }

  /**
   * @return EventDate[]
   */
  public function getEventDates(): array {
    return $this->eventDates()
      ->get()->all();
  }

  /**
   * @return EventDate[]
   */
  public function getEventDatesOrderedByDate(): array {
    return $this->eventDates()->orderBy('date')->get()->all();
  }

  /**
   * @return EventDate|HasMany
   */
  public function getFirstEventDate(): EventDate|HasMany {
    return $this->eventDates()->orderBy('date', 'ASC')->first();
  }

  /**
   * @return EventDate|HasMany
   */
  public function getLastEventDate(): EventDate|HasMany {
    return $this->eventDates()->latest('date')->first();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['for_everyone'] = $this->forEveryone;

    $returnable['decisions'] = $this->eventsDecisions();
    $returnable['dates'] = $this->getEventDatesOrderedByDate();

    $startDate = $this->getFirstEventDate();
    $endDate = $this->getLastEventDate();

    if ($startDate != null) {
      $startDate = $startDate->date;
    }
    $returnable['start_date'] = $startDate;

    if ($endDate != null) {
      $endDate = $endDate->date;
    }
    $returnable['end_date'] = $endDate;

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
