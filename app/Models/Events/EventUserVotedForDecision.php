<?php

namespace App\Models\Events;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
class EventUserVotedForDecision extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
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
    'updated_at'];

  /**
   * @return BelongsTo
   */
  public function decision() {
    return $this->belongsTo('App\Models\Events\EventDecision', 'decision_id')->first();
  }

  /**
   * @return BelongsTo
   */
  public function event() {
    return $this->belongsTo('App\Models\Events\Event')->first();
  }

  /**
   * @return BelongsTo
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')->first();
  }
}
