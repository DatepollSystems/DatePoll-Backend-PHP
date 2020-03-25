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
class EventForGroup extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'events_for_groups';

  /**
   * @var array
   */
  protected $fillable = [
    'event_id',
    'group_id',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | Event
   */
  public function event() {
    return $this->belongsTo('App\Models\Events\Event')->first();
  }

  /**
   * @return BelongsTo | Group
   */
  public function group() {
    return $this->belongsTo('App\Models\Groups\Group')->first();
  }
}
