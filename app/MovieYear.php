<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovieYear extends Model
{
  protected $fillable = [
    'year'
  ];

  public function getMovies()
  {
    return $this->hasMany('App\Movie');
  }
}
