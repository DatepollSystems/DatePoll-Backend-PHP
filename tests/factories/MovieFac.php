<?php

namespace Tests\Factories;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MovieYear;

class MovieFactory {
  public static function createMovieYear($number) {
    $movieYear = new MovieYear(['year' => $number]);
    $movieYear->save();

    return $movieYear;
  }

  public static function findAndDeleteMovieYear($number) {
    $testingMovieYear = MovieYear::where('year', '=', $number)
      ->first();
    if ($testingMovieYear != null) {
      $testingMovieYear->delete();
    }
  }

  public static function createMovie(MovieYear $movieYear) {
    $movie = new Movie([
      'name' => 'TestMovie',
      'date' => '2000-01-25',
      'trailerLink' => 'https://youtube.com',
      'posterLink' => 'https://test.at',
      'bookedTickets' => 12,
      'movie_year_id' => $movieYear->id, ]);
    $movie->save();

    return $movie;
  }

  public static function findMovieByName($movieName) {
    return Movie::where('name', '=', $movieName)
      ->first();
  }

  public static function findAndDeleteMovie($movieName) {
    $testingMovie = Movie::where('name', '=', $movieName)
      ->first();
    if ($testingMovie != null) {
      $testingMovie->delete();
    }
  }
}
