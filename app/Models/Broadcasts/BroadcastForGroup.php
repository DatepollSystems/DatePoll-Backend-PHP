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

  /**
   * @var array
   */
  protected $fillable = [
    'broadcast_id',
    'group_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Broadcast
   */
  public function broadcast(): BelongsTo|Broadcast {
    return $this->belongsTo(Broadcast::class)->first();
  }

  /**
   * @return BelongsTo | Group
   */
  public function group(): BelongsTo|Group {
    return $this->belongsTo(Group::class)->first();
  }
}
