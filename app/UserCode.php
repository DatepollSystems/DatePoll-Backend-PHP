<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $purpose
 * @property string $code
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserCode extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'purpose', 'code', 'rate_limit', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function generateCode() {
      return rand(100000, 999999);
    }
}
