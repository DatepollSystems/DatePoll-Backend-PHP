<?php

namespace App\Providers;

use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\Movie\MovieRepository;
use App\Repositories\Cinema\MovieBooking\IMovieBookingRepository;
use App\Repositories\Cinema\MovieBooking\MovieBookingRepository;
use App\Repositories\Cinema\MovieWorker\IMovieWorkerRepository;
use App\Repositories\Cinema\MovieWorker\MovieWorkerRepository;
use App\Repositories\Cinema\MovieYear\IMovieYearRepository;
use App\Repositories\Cinema\MovieYear\MovieYearRepository;
use App\Repositories\Files\File\FileRepository;
use App\Repositories\Files\File\IFileRepository;
use App\Repositories\Job\IJobRepository;
use App\Repositories\Job\JobRepository;
use App\Repositories\Log\ILogRepository;
use App\Repositories\Log\LogRepository;
use App\Repositories\Setting\ISettingRepository;
use App\Repositories\Setting\SettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\User\UserRepository;
use App\Repositories\User\UserToken\IUserTokenRepository;
use App\Repositories\User\UserToken\UserTokenRepository;
use Illuminate\Support\ServiceProvider;

class DatePollServiceProvider extends ServiceProvider
{
  public function register() {
    /** Cinema repositories */
    $this->app->bind(IMovieRepository::class, MovieRepository::class);
    $this->app->bind(IMovieWorkerRepository::class, MovieWorkerRepository::class);
    $this->app->bind(IMovieYearRepository::class, MovieYearRepository::class);
    $this->app->bind(IMovieBookingRepository::class, MovieBookingRepository::class);

    /** User repositories */
    $this->app->bind(IUserRepository::class, UserRepository::class);
    $this->app->bind(IUserTokenRepository::class, UserTokenRepository::class);

    /** System repositories */
    $this->app->bind(ISettingRepository::class, SettingRepository::class);
    $this->app->bind(IJobRepository::class, JobRepository::class);
    $this->app->bind(ILogRepository::class, LogRepository::class);
  }
}