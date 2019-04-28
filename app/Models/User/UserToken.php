<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $purpose
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserToken extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['user_id', 'token', 'purpose', 'description', 'created_at', 'updated_at'];

  /**
   * @return Model|BelongsTo|object
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')->first();
  }

  public static function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
}
