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
class BroadcastUserInfo extends Model {
  protected $table = 'broadcasts_users_info';

  /**
   * @var array
   */
  protected $fillable = [
    'broadcast_id',
    'user_id',
    'sent',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Broadcast
   */
  public function broadcast(): BelongsTo|Broadcast {
    return $this->belongsTo(Broadcast::class)->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['user_name'] = $this->user()->getCompleteName();
    return $returnable;
  }
}
