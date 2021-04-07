<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $purpose
 * @property string $code
 * @property int $rate_limit
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserCode extends Model {
  /**
   * @var array
   */
  protected $fillable = ['user_id', 'purpose', 'code', 'rate_limit', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo|User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }
}
