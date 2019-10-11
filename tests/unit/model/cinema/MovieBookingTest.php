<?php

require_once(__DIR__ . '/../../../factories/MovieFac.php');
require_once(__DIR__ . '/../../../factories/UserFac.php');

use App\Models\Cinema\MoviesBooking;
use Tests\Factories\MovieFactory;
use Tests\Factories\UserFactory;

class MovieBookingTest extends TestCase
{

  private function clearMovieBookingsTesting() {
    MovieFactory::findAndDeleteMovieYear(1234);
    MovieFactory::findAndDeleteMovie('TestMovie');
    UserFactory::findAndDeleteUser('test.user');
  }

  public function testClearMovieBookingsBeforeTesting() {
    $this->clearMovieBookingsTesting();
    $this->assertTrue(true);
  }

  public function testCreateMovieBooking() {
    $movieYear = MovieFactory::createMovieYear(1234);

    $movie = MovieFactory::createMovie($movieYear);
    $user = UserFactory::createUser('test.user');

    $movieBooking = new MoviesBooking([
      'user_id' => $user->id,
      'movie_id' => $movie->id,
      'amount' => 12]);
    $movieBooking->save();

    $this->assertSame(1, count($movie->moviesBookings()));
  }

  public function testRemoveMovieBooking() {
    $movie = MovieFactory::findMovieByName('TestMovie');
    $user = UserFactory::findUserByUsername('test.user');

    $movieBooking = MoviesBooking::where('user_id', $user->id)
                                 ->where('movie_id', $movie->id)
                                 ->first();
    $this->assertTrue($movieBooking->delete());
  }

  public function testClearMovieBookingsAfterTesting() {
    $this->clearMovieBookingsTesting();
    $this->assertTrue(true);
  }
}
