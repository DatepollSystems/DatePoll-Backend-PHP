<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserPermission extends Model {
  /**
   * @var array
   */
  protected $fillable = ['user_id', 'permission', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo|User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }
}
