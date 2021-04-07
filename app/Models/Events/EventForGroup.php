<?php

namespace App\Models\Events;

use App\Models\Groups\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property int $group_id
 * @property string $created_at
 * @property string $updated_at
 * @property Event $event
 * @property Group $group
 */
class EventForGroup extends Model {
  protected $table = 'events_for_groups';
  protected $with = ['event', 'group'];

  /**
   * @var array
   */
  protected $fillable = [
    'event_id',
    'group_id',
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
  public function group(): BelongsTo {
    return $this->belongsTo(Group::class);
  }
}
