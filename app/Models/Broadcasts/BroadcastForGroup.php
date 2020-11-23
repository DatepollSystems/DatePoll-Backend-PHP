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
  /**
   * The table associated with the model.
   *
   * @var string
   */
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
  public function broadcast() {
    return $this->belongsTo('App\Models\Broadcasts\Broadcast')->first();
  }

  /**
   * @return BelongsTo | Group
   */
  public function group() {
    return $this->belongsTo('App\Models\Groups\Group')->first();
  }
}
