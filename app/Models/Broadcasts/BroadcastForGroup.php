<?php

namespace App\Models\Broadcasts;

use App\Models\Groups\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $broadcast_id
 * @property int $group_id
 * @property string $created_at
 * @property string $updated_at
 * @property Broadcast $broadcast
 * @property Group $group
 */
class BroadcastForGroup extends Model {
  protected $table = 'broadcasts_for_groups';
  protected $with = ['broadcast', 'group'];

  /**
   * @var array
   */
  protected $fillable = [
    'broadcast_id',
    'group_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo
   */
  public function broadcast(): BelongsTo {
    return $this->belongsTo(Broadcast::class);
  }

  /**
   * @return BelongsTo
   */
  public function group(): BelongsTo {
    return $this->belongsTo(Group::class);
  }
}
