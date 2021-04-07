<?php

namespace App\Models\Broadcasts;

use App\Models\Subgroups\Subgroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $broadcast_id
 * @property int $subgroup_id
 * @property string $created_at
 * @property string $updated_at
 * @property Broadcast $broadcast
 * @property Subgroup $subgroup
 */
class BroadcastForSubgroup extends Model {
  protected $table = 'broadcasts_for_subgroups';
  protected $with = ['broadcast', 'subgroup'];

  /**
   * @var array
   */
  protected $fillable = [
    'broadcast_id',
    'subgroup_id',
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
  public function subgroup(): BelongsTo {
    return $this->belongsTo(Subgroup::class);
  }
}
