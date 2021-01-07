<?php

namespace App\Models\System;

use App\LogTypes;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property LogTypes $type
 * @property string $message
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 */
class Log extends Model {
  protected $table = 'logs';

  /**
   * @var array
   */
  protected $fillable = [
    'type',
    'message',
    'user_id',
    'created_at',
    'updated_at', ];

  /**
   * @return User|BelongsTo|null
   */
  public function user(): User|BelongsTo|null {
    return $this->belongsTo(User::class, 'user_id')
      ->first();
  }


  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['user_name'] = $this->user()?->getCompleteName();
    return $returnable;
  }
}
