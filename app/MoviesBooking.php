<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $movie_id
 * @property int $amount
 * @property string $created_at
 * @property string $updated_at
 * @property Movie $movie
 * @property User $user
 */
class MoviesBooking extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'movie_id', 'amount', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movie()
    {
        return $this->belongsTo('App\Movie');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
