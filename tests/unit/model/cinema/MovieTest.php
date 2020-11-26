<?php

require_once(__DIR__ . '/../../../factories/MovieFac.php');

use App\Models\Cinema\Movie;
use Tests\Factories\MovieFactory;

class MovieTest extends TestCase {
  private function clearMovieTesting() {
    MovieFactory::findAndDeleteMovieYear(1234);
    MovieFactory::findAndDeleteMovieYear(4321);

    MovieFactory::findAndDeleteMovie('TestMovie');
    MovieFactory::findAndDeleteMovie('TestMovieUpdated');
  }

  public function testClearMovieBeforeTesting() {
    $this->clearMovieTesting();
    $this->assertTrue(true);
  }

  public function testCreateMovie() {
    $movieYear = MovieFactory::createMovieYear(1234);

    $movie = MovieFactory::createMovie($movieYear);

    $fetchedMovie = Movie::find($movie->id);

    $this->assertSame($movie->id, $fetchedMovie->id);
    $this->assertSame($movie->name, $fetchedMovie->name);
    $this->assertSame($movie->date, $fetchedMovie->date);
    $this->assertSame($movie->trailerLink, $fetchedMovie->trailerLink);
    $this->assertSame($movie->posterLink, $fetchedMovie->posterLink);
    $this->assertSame($movie->bookedTickets, $fetchedMovie->bookedTickets);
    $this->assertSame($movie->movie_year_id, $fetchedMovie->movie_year_id);
  }

  public function testUpdateMovie() {
    $movie = Movie::where('name', '=', 'TestMovie')
      ->where('date', '=', '2000-01-25')
      ->first();
    if ($movie == null) {
      $this->fail('Could not update movie! Movie was not created!');
    }

    $movieYear = MovieFactory::createMovieYear(4321);
    $movie->name = 'TestMovieUpdated';
    $movie->date = '2000-01-26';
    $movie->trailerLink = 'https://peertube.org';
    $movie->posterLink = 'https://test.at/test';
    $movie->bookedTickets = 19;
    $movie->movie_year_id = $movieYear->id;

    $movie->save();

    $fetchedMovie = Movie::find($movie->id);

    $this->assertSame($movie->id, $fetchedMovie->id);
    $this->assertSame($movie->name, $fetchedMovie->name);
    $this->assertSame($movie->date, $fetchedMovie->date);
    $this->assertSame($movie->trailerLink, $fetchedMovie->trailerLink);
    $this->assertSame($movie->posterLink, $fetchedMovie->posterLink);
    $this->assertSame($movie->bookedTickets, $fetchedMovie->bookedTickets);
    $this->assertSame($movie->movie_year_id, $fetchedMovie->movie_year_id);
  }

  public function testDeleteMovie() {
    $movie = Movie::where('name', '=', 'TestMovieUpdated')
      ->where('date', '=', '2000-01-26')
      ->first();
    if ($movie == null) {
      $this->fail('Could not delete movie! Movie was not updated!');
    }

    $movie->delete();
    $fetchedMovie = Movie::find($movie->id);
    $this->assertSame(null, $fetchedMovie);
  }

  public function testClearMovieAfterTesting() {
    $this->clearMovieTesting();
    $this->assertTrue(true);
  }
}
