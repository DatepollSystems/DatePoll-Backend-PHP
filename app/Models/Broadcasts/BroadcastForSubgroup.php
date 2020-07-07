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
class BroadcastForSubgroup extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'broadcasts_for_subgroups';

  /**
   * @var array
   */
  protected $fillable = [
    'broadcast_id',
    'subgroup_id',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | Broadcast
   */
  public function event() {
    return $this->belongsTo('App\Models\Broadcasts\Broadcast')->first();
  }

  /**
   * @return BelongsTo | Subgroup
   */
  public function subgroup() {
    return $this->belongsTo('App\Models\Subgroups\Subgroup')->first();
  }
}
