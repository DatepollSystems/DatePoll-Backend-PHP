<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $label
 * @property string $number
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserTelephoneNumber extends Model {
  /**
   * @var string[]
   */
  protected $fillable = ['user_id', 'label', 'number', 'created_at', 'updated_at'];

  /**
   * @var string[]
   */
  protected $hidden = ['user_id', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo|User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }
}
