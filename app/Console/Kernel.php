<?php

namespace App\Console;

use App\Console\Commands\AddAdminUser;
use App\Console\Commands\DatePollCleanUp;
use App\Console\Commands\DropDatabase;
use App\Console\Commands\ProcessBroadcastEmailsInInbox;
use App\Console\Commands\ReQueueNotSentBroadcasts;
use App\Console\Commands\SetupDatePoll;
use App\Console\Commands\UpdateDatePollDB;
use App\Utils\EnvironmentHelper;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
    AddAdminUser::class,
    DropDatabase::class,
    SetupDatePoll::class,
    UpdateDatePollDB::class,
    ReQueueNotSentBroadcasts::class,
    ProcessBroadcastEmailsInInbox::class,
    DatePollCleanUp::class,
  ];

  /**
   * Define the application's command schedule.
   *
   * @param Schedule $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule): void {
    if (EnvironmentHelper::isDebug()) {
      $schedule->command(ProcessBroadcastEmailsInInbox::class)->everyMinute();
    } else {
      $schedule->command(ProcessBroadcastEmailsInInbox::class)->everyFourMinutes();
    }
    $schedule->command(DatePollCleanUp::class, ['--force'])->daily();
  }
}
