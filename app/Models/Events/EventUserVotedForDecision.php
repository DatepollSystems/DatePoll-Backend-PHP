<?php

namespace App\Models\Events;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @property int $id
 * @property string $additionalInformation
 * @property int $event_id
 * @property int $user_id
 * @property int $decision_id
 * @property string $created_at
 * @property string $updated_at
 * @property EventDecision $eventsDecision
 * @property Event $event
 * @property User $user
 */
class EventUserVotedForDecision extends Model {
  protected $table = 'events_users_voted_for';

  /**
   * @var array
   */
  protected $fillable = [
    'additionalInformation',
    'event_id',
    'user_id',
    'decision_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | EventDecision
   */

  public function decision(): BelongsTo|EventDecision {
    return $this->belongsTo(EventDecision::class, 'decision_id')->first();
  }

  /**
   * @return BelongsTo | Event
   */
  public function event(): BelongsTo|Event {
    return $this->belongsTo(Event::class)->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }

  /**
   * @return array
   */
  #[ArrayShape(['id' => "int",
                                    'decision' => "string",
                                    'event_id' => "int",
                                    'show_in_calendar' => "bool",
                                    'color' => "string",
                                    'created_at' => "string",
                                    'updated_at' => "string",
                                    'additional_information' => "string"])]
  public function toArray(): array {
    $decision = $this->decision();
    return [
      'id' => $decision->id,
      'decision' => $decision->decision,
      'event_id' => $this->event_id,
      'show_in_calendar' => $decision->showInCalendar,
      'color' => $decision->color,
      'created_at' => $decision->created_at,
      'updated_at' => $decision->updated_at,
      'additional_information' => $this->additionalInformation
    ];
  }
}
