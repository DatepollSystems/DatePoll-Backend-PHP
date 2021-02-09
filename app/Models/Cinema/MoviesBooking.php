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
  protected $with = ['user', 'movie'];

  /**
   * @var array
   */
  protected $fillable = ['user_id', 'movie_id', 'amount', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo
   */
  public function movie(): BelongsTo {
    return $this->belongsTo(Movie::class);
  }

  /**
   * @return BelongsTo
   */
  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }
}
