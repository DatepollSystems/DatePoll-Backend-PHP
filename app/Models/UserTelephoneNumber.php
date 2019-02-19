<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $label
 * @property string $number
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserTelephoneNumber extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'label', 'number', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
