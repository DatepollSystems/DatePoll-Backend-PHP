<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserPermission extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'permission', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
