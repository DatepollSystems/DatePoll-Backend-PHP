<?php

namespace App\Models\Cinema;

use Illuminate\Database\Eloquent\Model;

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
   * @return Movie[]
   */
  public function movies(): array {
    return $this->hasMany(Movie::class)->get()->all();
  }
}
