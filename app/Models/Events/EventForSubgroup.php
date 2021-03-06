<?php

namespace App\Models\Events;

use App\Models\Subgroups\Subgroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property int $subgroup_id
 * @property string $created_at
 * @property string $updated_at
 * @property Event $event
 * @property Subgroup $subgroup
 */
class EventForSubgroup extends Model {
  protected $table = 'events_for_subgroups';
  protected $with = ['event', 'subgroup'];

  /**
   * @var array
   */
  protected $fillable = [
    'event_id',
    'subgroup_id',
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
  public function subgroup(): BelongsTo {
    return $this->belongsTo(Subgroup::class);
  }
}
