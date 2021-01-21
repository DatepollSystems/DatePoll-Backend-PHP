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
 * @property User $editor
 */
class UserChange extends Model {
  protected $table = 'users_changes';

  /**
   * @var array
   */
  protected $fillable = [
    'user_id',
    'editor_id',
    'property',
    'old_value',
    'new_value',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class, 'user_id')
      ->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function editor(): BelongsTo|User {
    return $this->belongsTo(User::class, 'editor_id')
      ->first();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable =  parent::toArray();
    $returnable['editor_name'] = $this->editor()->getCompleteName();
    $returnable['user_name'] = $this->user()->getCompleteName();
    return $returnable;
  }
}
