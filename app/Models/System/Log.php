<?php

namespace App\Models\System;

use App\LogTypes;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use stdClass;

/**
 * @property int $id
 * @property LogTypes $type
 * @property string $message
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 */
class Log extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'logs';

  /**
   * @var array
   */
  protected $fillable = [
    'type',
    'message',
    'user_id',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | User
   */
  public function user() {
    return $this->belongsTo(User::class, 'user_id')
                ->first();
  }

  /**
   * @return Log | stdClass
   */
  public function getReturnable() {
    $returnable = $this;

    if ($this->user_id != null) {
      $returnable->user_name = $this->user()->getName();
    }

    return $returnable;
  }
}
