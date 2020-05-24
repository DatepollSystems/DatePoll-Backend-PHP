<?php namespace App\Console\Commands;

use App\Logging;
use App\LogTypes;
use App\Models\Events\Event;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Versions;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateDatePollDB extends ACommand
{
  protected $settingRepository = null;
  protected $eventRepository = null;
  protected $eventDateRepository = null;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'update-datepoll-db';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Runs database migrations until versions are matching.';

  /**
   * Create a new command instance.
   *
   * @param ISettingRepository $settingRepository
   * @param IEventRepository $eventRepository
   * @param IEventDateRepository $eventDateRepository
   */
  public function __construct(ISettingRepository $settingRepository, IEventRepository $eventRepository, IEventDateRepository $eventDateRepository) {
    parent::__construct();

    $this->settingRepository = $settingRepository;
    $this->eventRepository = $eventRepository;
    $this->eventDateRepository = $eventDateRepository;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle() {
    $this->log('handle', 'Application database version: ' . Versions::getApplicationDatabaseVersion(), LogTypes::INFO);
    $this->log('handle', 'Current database version: ' . $this->settingRepository->getCurrentDatabaseVersion(), LogTypes::INFO);

    if (Versions::getApplicationDatabaseVersion() === $this->settingRepository->getCurrentDatabaseVersion()) {
      $this->log('handle', 'Application and current database version match, nothing to do! Aborting...', LogTypes::INFO);
      return;
    }

    if (!$this->askBooleanQuestion('Start with database migration?')) {
      $this->log('handle', 'User aborting database migration...', LogTypes::INFO);
      return;
    }

    while (Versions::getApplicationDatabaseVersion() > $this->settingRepository->getCurrentDatabaseVersion()) {
      $versionToMigrateTo = $this->settingRepository->getCurrentDatabaseVersion() + 1;

      switch ($versionToMigrateTo) {
        case 1:
          if (!$this->migrateDatabaseVersionFrom0To1()) {
            $this->log('handle', 'Migration failed!', LogTypes::WARNING);
            return;
          }
          break;
        case 2:
          if (!$this->migrateDatabaseVersionFrom1To2()) {
            $this->log('handle', 'Migration failed!', LogTypes::WARNING);
            return;
          }
          break;
      }

      $this->log('handle', 'Saving new database version', LogTypes::INFO);
      $this->settingRepository->setCurrentDatabaseVersion($versionToMigrateTo);

      if (!$this->askBooleanQuestion('Continue with database migration?')) {
        $this->log('handle', 'User aborting database migration...', LogTypes::INFO);
        return;
      }
    }

    $this->log('handle', 'Database update finished!', LogTypes::INFO);

    return;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom0To1(): bool {
    $this->log('db-migrate-0To1', 'Running migration from 0 to 1', LogTypes::INFO);
    $this->log('db-migrate-0To1', 'Running event startDate, endDate migrations...', LogTypes::INFO);
    foreach ($this->eventRepository->getAllEvents() as $event) {
      try {
        $startDate = DB::selectOne('SELECT startDate From events WHERE id = ?', [$event->id])->startDate;
        $endDate = DB::selectOne('SELECT endDate From events WHERE id = ?', [$event->id])->endDate;
      } catch (Exception $exception) {
        $this->log('db-migrate-0To1', 'Could not get startDate or endDate! Cancelling...', LogTypes::WARNING);
        return false;
      }

      $this->log('db-migrate-0To1', 'Event - ' . $event->id . ' | startDate: ' . $startDate . ' | endDate: ' . $endDate, LogTypes::INFO);
      $this->eventDateRepository->createEventDate($event, -199, -199, $startDate, null, null);
      $this->eventDateRepository->createEventDate($event, -199, -199, $endDate, null, null);
    }

    $this->log('db-migrate-0To1', 'Running event startDate, endDate migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-0To1', 'Running database migrations...', LogTypes::INFO);

    try {
      $this->runDbStatement('0To1', 'ALTER TABLE user_tokens DROP INDEX user_tokens_token_unique;');
      $this->runDbStatement('0To1', 'ALTER TABLE events DROP COLUMN location;');
      $this->runDbStatement('0To1', 'ALTER TABLE events DROP COLUMN startDate;');
      $this->runDbStatement('0To1', 'ALTER TABLE events DROP COLUMN endDate;');
    } catch (Exception $exception) {
      $this->log('db-migrate-0To1', 'Database migrations failed!', LogTypes::WARNING);
      return false;
    }
    $this->log('db-migrate-0To1', 'Running database migrations finished!', LogTypes::INFO);

    $this->log('db-migrate-0To1', 'Running migration from 0 to 1 finished!', LogTypes::INFO);
    return true;
  }


  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom1To2(): bool {
    $this->log('db-migrate-0To1', 'Running migration from 1 to 2', LogTypes::INFO);

    $this->log('db-migrate-0To1', 'Running events decisions color migrations...', LogTypes::INFO);
    try {
      $this->runDbStatement('1To2', 'ALTER TABLE events_decisions ADD color varchar(7) NOT NULL DEFAULT \'#ffffff\';');
    } catch (Exception $exception) {
      $this->log('db-migrate-1To2', 'Database migrations failed!', LogTypes::WARNING);
      return false;
    }

    $this->log('db-migrate-0To1', 'Running event standard decisions color migrations...', LogTypes::INFO);
    try {
      $this->runDbStatement('1To2', 'ALTER TABLE events_standard_decisions ADD color varchar(7) NOT NULL DEFAULT \'#ffffff\';');
    } catch (Exception $exception) {
      $this->log('db-migrate-1To2', 'Database migrations failed!', LogTypes::WARNING);
      return false;
    }

    $this->log('db-migrate-1To2', 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-1To2', 'Running migration from 1 to 2 finished!', LogTypes::INFO);
    return true;
  }

  /**
   * @param string $migration
   * @param string $statement
   * @throws Exception
   */
  private function runDbStatement(string $migration, string $statement) {
    try {
      DB::statement($statement);
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $migration, 'Statement failed: "' . $statement . '" | Error message: ' . $exception->getMessage(), LogTypes::WARNING);
      throw new Exception('Migration error...');
    }
  }
}