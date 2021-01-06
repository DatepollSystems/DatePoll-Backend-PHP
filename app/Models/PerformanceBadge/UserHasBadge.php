<?php

namespace App\Models\PerformanceBadge;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $description
 * @property string $getDate
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserHasBadge extends Model {
  protected $table = 'users_has_badges';

  /**
   * @var array
   */
  protected $fillable = [
    'description',
    'getDate',
    'reason',
    'user_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)
      ->first();
  }
}
