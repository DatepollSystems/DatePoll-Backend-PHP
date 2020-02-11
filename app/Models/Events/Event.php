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
   * @return Collection | EventDecision[]
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
   * @return Collection | EventDate[]
   */
  public function getEventDates() {
    return $this->hasMany('App\Models\Events\EventDate', 'event_id')
                ->get();
  }
}
