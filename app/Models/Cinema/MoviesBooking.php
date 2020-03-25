<?php

namespace App\Models\Cinema;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
   * @return Movie|Model|BelongsTo|object|null
   */
  public function movie() {
    return $this->belongsTo('App\Models\Cinema\Movie')->first();
  }

  /**
   * @return User|Model|BelongsTo|object|null
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')->first();
  }
}
