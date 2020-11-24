<?php

namespace App\Models\Cinema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $year
 * @property string $created_at
 * @property string $updated_at
 * @property Movie[] $movies
 */
class MovieYear extends Model {
  /**
   * @var array
   */
  protected $fillable = ['year', 'created_at', 'updated_at'];

  /**
   * @return HasMany
   */
  public function movies() {
    return $this->hasMany('App\Models\Cinema\Movie');
  }
}
