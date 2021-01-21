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
class MoviesBooking extends Model {
  /**
   * @var array
   */
  protected $fillable = ['user_id', 'movie_id', 'amount', 'created_at', 'updated_at'];

  /**
   * @return Movie|BelongsTo
   */
  public function movie(): BelongsTo|Movie {
    return $this->belongsTo(Movie::class)->first();
  }

  /**
   * @return User|BelongsTo|null
   */
  public function user(): BelongsTo|User|null {
    return $this->belongsTo(User::class)->first();
  }
}
