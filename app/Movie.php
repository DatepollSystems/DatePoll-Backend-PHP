<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{

  protected $fillable = [
    'name', 'date', 'trailerLink', 'posterLink', 'bookedTickets', 'movie_year_id'
  ];

  public function getVisitors() {
    return $this->belongsToMany(User::class);
  }

  public function getYear()
  {
    return $this->belongsTo('App\MovieYear', 'movie_year_id', 'id');
  }
}
