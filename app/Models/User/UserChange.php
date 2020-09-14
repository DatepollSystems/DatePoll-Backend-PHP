<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $editor_id
 * @property string $property
 * @property string $old_value
 * @property string $new_value
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserChange extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['user_id', 'editor_id', 'property', 'old_value', 'new_value', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo
   */
  public function user() {
    return $this->belongsTo(User::class, 'user_id')
                ->first();
  }

  /**
   * @return BelongsTo
   */
  public function editor() {
    return $this->belongsTo(User::class, 'editor_id')
                ->first();
  }
}
