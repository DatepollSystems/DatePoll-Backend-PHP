<?php

namespace App\Models\Broadcasts;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $broadcast_id
 * @property int $user_id
 * @property boolean $sent
 * @property string $created_at
 * @property string $updated_at
 * @property Broadcast $broadcast
 * @property User $user
 */
class BroadcastUserInfo extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'broadcasts_users_info';

  /**
   * @var array
   */
  protected $fillable = [
    'broadcast_id',
    'user_id',
    'sent',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | Broadcast
   */
  public function broadcast() {
    return $this->belongsTo('App\Models\Broadcasts\Broadcast')->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')->first();
  }
}
