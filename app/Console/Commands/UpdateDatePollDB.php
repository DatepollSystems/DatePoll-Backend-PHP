<?php /** @noinspection SqlResolve SqlNoDataSourceInspection */

namespace App\Console\Commands;

use App\Logging;
use App\LogTypes;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Versions;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateDatePollDB extends ACommand
{
  protected ISettingRepository $settingRepository;
  protected IEventRepository $eventRepository;
  protected IEventDateRepository $eventDateRepository;

  protected $signature = 'update-datepoll-db';
  protected $description = 'Runs database migrations until versions are matching.';

  public function __construct(ISettingRepository $settingRepository, IEventRepository $eventRepository,
                              IEventDateRepository $eventDateRepository) {
    parent::__construct();

    $this->settingRepository = $settingRepository;
    $this->eventRepository = $eventRepository;
    $this->eventDateRepository = $eventDateRepository;
  }

  /**
   * @return void
   */
  public function handle() {
    $this->log('handle', 'Application database version: ' . Versions::getApplicationDatabaseVersion(), LogTypes::INFO);
    $this->log('handle', 'Current database version: ' . $this->settingRepository->getCurrentDatabaseVersion(),
      LogTypes::INFO);

    if (Versions::getApplicationDatabaseVersion() === $this->settingRepository->getCurrentDatabaseVersion()) {
      $this->log('handle', 'Application and current database version match, nothing to do! Aborting...',
        LogTypes::INFO);
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
        case 3:
          if (!$this->migrateDatabaseVersionFrom2To3()) {
            $this->log('handle', 'Migration failed!', LogTypes::WARNING);
            return;
          }
          break;
        case 4:
          if (!$this->migrateDatabaseVersionFrom3To4()) {
            $this->log('handle', 'Migration failed!', LogTypes::WARNING);
            return;
          }
          break;
        case 5:
          if (!$this->migrateDatabaseVersionFrom4To5()) {
            $this->log('handle', 'Migration failed!', LogTypes::WARNING);
            return;
          }
          break;
        case 6:
          if (!$this->migrateDatabaseVersionFrom5To6()) {
            $this->log('handle', 'Migration failed!', LogTypes::WARNING);
            return;
          }
          break;
        case 7:
          if (!$this->migrateDatabaseVersionFrom6To7()) {
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
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom6To7(): bool {
    $version = '6To7';
    $this->log('db-migrate-' . $version, 'Running migration from 6 to 7', LogTypes::INFO);

    $this->log('db-migrate-' . $version, 'Altering table dates migrate date from varchar to date', LogTypes::INFO);
    try {
      $this->runDbStatement($version, 'ALTER TABLE event_dates ADD date_dt DATETIME;');
      $this->runDbStatement($version, 'UPDATE event_dates SET date_dt = STR_TO_DATE(event_dates.date, \'%Y-%c-%d %H:%i:%s\');');
      $this->runDbStatement($version, 'ALTER TABLE event_dates DROP date, RENAME COLUMN date_dt TO date;');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-' . $version, 'Running migration from 6 to 7 finished!', LogTypes::INFO);
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom5To6(): bool {
    $version = '5To6';
    $this->log('db-migrate-' . $version, 'Running migration from 5 to 6', LogTypes::INFO);

    $this->log('db-migrate-' . $version, 'Altering table logs adding user id foreign key', LogTypes::INFO);
    try {
      $this->runDbStatement($version, 'ALTER TABLE logs ADD COLUMN user_id INT UNSIGNED;');
      $this->runDbStatement($version, 'ALTER TABLE logs ADD FOREIGN KEY (user_id) REFERENCES `users` (`id`);');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-' . $version, 'Running migration from 5 to 6 finished!', LogTypes::INFO);
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom4To5(): bool {
    $version = '4To5';
    $this->log('db-migrate-' . $version, 'Running migration from 4 to 5', LogTypes::INFO);

    $this->log('db-migrate-' . $version, 'Altering table movies drop worker foreign keys...', LogTypes::INFO);
    try {
      $this->runDbStatement($version, 'ALTER TABLE movies DROP FOREIGN KEY movies_emergency_worker_id_foreign;');
      $this->runDbStatement($version, 'ALTER TABLE movies DROP FOREIGN KEY movies_worker_id_foreign;');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Altering table movies add new foreign keys...', LogTypes::INFO);
    try {
      $this->runDbStatement($version,
                            'ALTER TABLE movies ADD FOREIGN KEY (emergency_worker_id) REFERENCES `users` (`id`);');
      $this->runDbStatement($version, 'ALTER TABLE movies ADD FOREIGN KEY (worker_id) REFERENCES `users` (`id`);');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Altering table broadcasts changing writer foreign key...', LogTypes::INFO);
    try {
      $this->runDbStatement($version, 'ALTER TABLE broadcasts DROP FOREIGN KEY broadcasts_writer_user_id_foreign;');
      $this->runDbStatement($version,
                            'ALTER TABLE broadcasts ADD FOREIGN KEY (writer_user_id) REFERENCES `users` (`id`);');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Altering table groups and subgroups adding oderN INT NOT NULL DEFAULT 0',
               LogTypes::INFO);
    try {
      $this->runDbStatement($version, 'ALTER TABLE \'groups\' ADD orderN INT NOT NULL DEFAULT 0;');
      $this->runDbStatement($version, 'ALTER TABLE subgroups ADD orderN INT NOT NULL DEFAULT 0;');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Changing bv_member to varchar(191) and altering data', LogTypes::INFO);
    try {
      $this->runDbStatement($version, 'ALTER TABLE users MODIFY bv_member VARCHAR (191) NOT NULL;');
      $this->runDbStatement($version, 'UPDATE users SET bv_member = \'gemeldet\' where bv_member = \'1\';');
      $this->runDbStatement($version, 'UPDATE users SET bv_member = \'\' where bv_member = \'0\';');
    } catch (Exception $exception) {
      $this->log('db-migrate-' . $version, 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-' . $version, 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-' . $version, 'Running migration from 4 to 5 finished!', LogTypes::INFO);
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom3To4(): bool {
    $this->log('db-migrate-3To4', 'Running migration from 3 to 4', LogTypes::INFO);

    $this->log('db-migrate-3To4', 'Altering table user drop member_number', LogTypes::INFO);
    try {
      $this->runDbStatement('3To4', 'ALTER TABLE users DROP member_number;');
    } catch (Exception $exception) {
      $this->log('db-migrate-3To4', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-3To4', 'Altering table user add member_number', LogTypes::INFO);
    try {
      $this->runDbStatement('3To4', 'ALTER TABLE users ADD member_number VARCHAR(191) DEFAULT null;');
    } catch (Exception $exception) {
      $this->log('db-migrate-3To4', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-3To4', 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-3To4', 'Running migration from 3 to 4 finished!', LogTypes::INFO);
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom2To3(): bool {
    $this->log('db-migrate-2To3', 'Running migration from 2 to 3', LogTypes::INFO);

    $this->log('db-migrate-2To3', 'Deleting jobs table...', LogTypes::INFO);
    try {
      $this->runDbStatement('2To3', 'DROP TABLE jobs;');
    } catch (Exception $exception) {
      $this->log('db-migrate-2To3', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-2To3', 'Altering table user add internal_comment', LogTypes::INFO);
    try {
      $this->runDbStatement('2To3', 'ALTER TABLE users ADD internal_comment TEXT NULL;');
    } catch (Exception $exception) {
      $this->log('db-migrate-2To3', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-2To3', 'Altering table user add information_denied', LogTypes::INFO);
    try {
      $this->runDbStatement('2To3', 'ALTER TABLE users ADD information_denied TINYINT DEFAULT 0 NOT NULL;');
    } catch (Exception $exception) {
      $this->log('db-migrate-2To3', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-2To3', 'Altering table user add member_number', LogTypes::INFO);
    try {
      $this->runDbStatement('2To3', 'ALTER TABLE users ADD member_number INTEGER DEFAULT NULL;');
    } catch (Exception $exception) {
      $this->log('db-migrate-2To3', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-2To3', 'Altering table user add bv_member', LogTypes::INFO);
    try {
      $this->runDbStatement('2To3', 'ALTER TABLE users ADD bv_member TINYINT DEFAULT 0 NOT NULL;');
    } catch (Exception $exception) {
      $this->log('db-migrate-2To3', 'Database migrations failed!', LogTypes::ERROR);
      return false;
    }

    $this->log('db-migrate-2To3', 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-2To3', 'Running migration from 2 to 3 finished!', LogTypes::INFO);
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom1To2(): bool {
    $this->log('db-migrate-1To2', 'Running migration from 1 to 2', LogTypes::INFO);

    $this->log('db-migrate-1To2', 'Running events decisions color migrations...', LogTypes::INFO);
    try {
      $this->runDbStatement('1To2', 'ALTER TABLE events_decisions ADD color varchar(7) NOT NULL DEFAULT \'#ffffff\';');
    } catch (Exception $exception) {
      $this->log('db-migrate-1To2', 'Database migrations failed!', LogTypes::WARNING);
      return false;
    }

    $this->log('db-migrate-0To1', 'Running event standard decisions color migrations...', LogTypes::INFO);
    try {
      $this->runDbStatement('1To2',
                            'ALTER TABLE events_standard_decisions ADD color varchar(7) NOT NULL DEFAULT \'#ffffff\';');
    } catch (Exception $exception) {
      $this->log('db-migrate-1To2', 'Database migrations failed!', LogTypes::WARNING);
      return false;
    }

    $this->log('db-migrate-1To2', 'Running database migrations finished!', LogTypes::INFO);
    $this->log('db-migrate-1To2', 'Running migration from 1 to 2 finished!', LogTypes::INFO);
    return true;
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

      $this->log('db-migrate-0To1', 'Event - ' . $event->id . ' | startDate: ' . $startDate . ' | endDate: ' . $endDate,
        LogTypes::INFO);
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
   * @param string $function
   * @param string $comment
   * @param string $type
   */
  private function log(string $function, string $comment, string $type) {
    switch ($type) {
      case LogTypes::INFO:
        Logging::info($function, $comment);
        break;

      case LogTypes::WARNING:
        Logging::warning($function, $comment);
        break;

      default:
        Logging::error('ACommand log', 'Unknown log type!');
        break;
    }

    $this->comment($function . ' | ' . $comment);
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
      $this->log('db-migrate-' . $migration,
        'Statement failed: "' . $statement . '" | Error message: ' . $exception->getMessage(), LogTypes::WARNING);
      throw new Exception('Migration error...');
    }
  }
}
