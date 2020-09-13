<?php

namespace App\Console;

use App\Console\Commands\ReQueueNotSentBroadcasts;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Laravelista\LumenVendorPublish\VendorPublishCommand;

class Kernel extends ConsoleKernel
{
  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
    'App\Console\Commands\AddAdminUser',
    'App\Console\Commands\DropTables',
    'App\Console\Commands\SetupDatePoll',
    'App\Console\Commands\UpdateDatePollDB',
    ReQueueNotSentBroadcasts::class,
    VendorPublishCommand::class
  ];

  /**
   * Define the application's command schedule.
   *
   * @param Schedule $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    //
  }
}
