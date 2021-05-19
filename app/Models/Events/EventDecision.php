<?php

namespace App\Models\Events;

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
  protected $table = 'events_decisions';

  protected $hidden = ['showInCalendar', 'event_id', 'created_at', 'updated_at'];

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
   * @return BelongsTo | Event
   */
  public function event(): BelongsTo|Event {
    return $this->belongsTo(Event::class)->first();
  }

  /**
   * @return EventUserVotedForDecision[]
   */
  public function eventsUsersVotedFor(): array {
    return $this->hasMany(EventUserVotedForDecision::class, 'decision_id')->get()->all();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['event_id'] = $this->event_id;
    $returnable['show_in_calendar'] = $this->showInCalendar;
    return $returnable;
  }
}
