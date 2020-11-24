<?php

require_once(__DIR__ . '/../../../factories/MovieFac.php');

use App\Models\Cinema\MovieYear;
use Tests\Factories\MovieFactory;

class MovieYearTest extends TestCase {
  private function clearMovieYearTesting() {
    MovieFactory::findAndDeleteMovieYear(1234);
    MovieFactory::findAndDeleteMovieYear(4321);
  }

  public function testClearMovieYearBeforeTesting() {
    $this->clearMovieYearTesting();
    $this->assertTrue(true);
  }

  public function testCreateMovieYear() {
    $movieYear = MovieFactory::createMovieYear(1234);

    $fetchedMovieYear = MovieYear::find($movieYear->id);

    $this->assertSame($movieYear->id, $fetchedMovieYear->id);
    $this->assertSame($movieYear->year, $fetchedMovieYear->year);
  }

  public function testUpdateMovieYear() {
    $movieYear = MovieYear::where('year', '=', 1234)
      ->first();
    if ($movieYear == null) {
      $this->fail('Could not update movie year! Movie year was not created!');
    }

    $movieYear->year = 4321;
    $movieYear->save();

    $fetchedMovieYear = MovieYear::find($movieYear->id);
    $this->assertSame($movieYear->id, $fetchedMovieYear->id);
    $this->assertSame($movieYear->year, $fetchedMovieYear->year);
  }

  public function testDeleteMovieYear() {
    $movieYear = MovieYear::where('year', '=', 4321)
      ->first();
    if ($movieYear == null) {
      $this->fail('Could not delete movie year! Movie year was not updated!');
    }

    $movieYear->delete();
    $fetchedMovieYear = MovieYear::find($movieYear->id);
    $this->assertSame(null, $fetchedMovieYear);
  }

  public function testClearMovieYearAfterTesting() {
    $this->clearMovieYearTesting();
    $this->assertTrue(true);
  }
}
