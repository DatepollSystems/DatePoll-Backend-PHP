<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DatePollServiceProvider extends ServiceProvider
{
  public function register() {
    /** Cinema repositories */
    $this->app->bind('App\Repositories\Cinema\Movie\IMovieRepository', 'App\Repositories\Cinema\Movie\MovieRepository');
    $this->app->bind('App\Repositories\Cinema\MovieWorker\IMovieWorkerRepository', 'App\Repositories\Cinema\MovieWorker\MovieWorkerRepository');
    $this->app->bind('App\Repositories\Cinema\MovieYear\IMovieYearRepository', 'App\Repositories\Cinema\MovieYear\MovieYearRepository');
    $this->app->bind('App\Repositories\Cinema\MovieBooking\IMovieBookingRepository', 'App\Repositories\Cinema\MovieBooking\MovieBookingRepository');

    /** User repositories */
    $this->app->bind('App\Repositories\User\User\IUserRepository', 'App\Repositories\User\User\UserRepository');

    /** System repositories */
    $this->app->bind('App\Repositories\Log\ILogRepository', 'App\Repositories\Log\LogRepository');
  }
}