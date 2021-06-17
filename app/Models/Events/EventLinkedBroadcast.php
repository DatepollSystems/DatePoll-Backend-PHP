<?php

namespace App\Models\Events;

use App\Models\Broadcasts\Broadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property int $broadcast_id
 * @property string $created_at
 * @property string $updated_at
 * @property Event $event
 * @property Broadcast $group
 */
class EventLinkedBroadcast extends Model {
  protected $table = 'events_linked_broadcasts';
  protected $with = ['event', 'broadcast'];

  /**
   * @var array
   */
  protected $fillable = [
    'event_id',
    'broadcast_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo
   */
  public function event(): BelongsTo {
    return $this->belongsTo(Event::class);
  }

  /**
   * @return BelongsTo
   */
  public function broadcast(): BelongsTo {
    return $this->belongsTo(Broadcast::class);
  }
}
