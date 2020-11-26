<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $decision
 * @property boolean $showInCalendar
 * @property string $color
 * @property string $created_at
 * @property string $updated_at
 * @property Event $event
 * @property EventUserVotedForDecision[] $eventsUsersVotedFor
 */
class EventDecision extends Model {
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'events_decisions';

  /**
   * @var array
   */
  protected $fillable = [
    'event_id',
    'decision',
    'showInCalendar',
    'color',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Event | null
   */
  public function event() {
    return $this->belongsTo('App\Models\Events\Event')->first();
  }

  /**
   * @return Collection
   */
  public function eventsUsersVotedFor() {
    return $this->hasMany('App\Models\Events\EventUserVotedForDecision', 'decision_id')->get();
  }
}
