<?php /** @noinspection PhpUnusedLocalVariableInspection SqlResolve SqlNoDataSourceInspection */

namespace App\Console\Commands;

use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Versions;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateDatePollDB extends ACommand {
  protected $signature = 'update-datepoll-db';
  protected $description = 'Runs database migrations until versions are matching.';

  public function __construct(
    private ISettingRepository $settingRepository,
    private IEventRepository $eventRepository,
    private IEventDateRepository $eventDateRepository
  ) {
    parent::__construct();
  }

  /**
   * @return void
   */
  public function handle() {
    $this->comment('Application database version: ' . Versions::getApplicationDatabaseVersion());

    $this->comment('Current database version: ' . $this->settingRepository->getCurrentDatabaseVersion());

    if (Versions::getApplicationDatabaseVersion() === $this->settingRepository->getCurrentDatabaseVersion()) {
      $this->comment('Application and current database version match, nothing to do! Aborting...');

      return;
    }

    if (! $this->askBooleanQuestion('Start with database migration?')) {
      $this->comment('User aborting database migration...');

      return;
    }

    while (Versions::getApplicationDatabaseVersion() > $this->settingRepository->getCurrentDatabaseVersion()) {
      $versionToMigrateTo = $this->settingRepository->getCurrentDatabaseVersion() + 1;

      switch ($versionToMigrateTo) {
        case 1:
          if (! $this->migrateDatabaseVersionFrom0To1()) {
            $this->warn('Migration failed!');

            return;
          }
          break;
        case 2:
          if (! $this->migrateDatabaseVersionFrom1To2()) {
            $this->warn('Migration failed!');

            return;
          }
          break;
        case 3:
          if (! $this->migrateDatabaseVersionFrom2To3()) {
            $this->warn('Migration failed!');

            return;
          }
          break;
        case 4:
          if (! $this->migrateDatabaseVersionFrom3To4()) {
            $this->warn('Migration failed!');

            return;
          }
          break;
        case 5:
          if (! $this->migrateDatabaseVersionFrom4To5()) {
            $this->warn('Migration failed!');

            return;
          }
          break;
        case 6:
          if (! $this->migrateDatabaseVersionFrom5To6()) {
            $this->warn('Migration failed!');

            return;
          }
          break;
        case 7:
          if (! $this->migrateDatabaseVersionFrom6To7()) {
            $this->error('Migration failed!');

            return;
          }
          break;
        case 8:
          if (! $this->migrateDatabaseVersionFrom7To8()) {
            $this->error('Migration failed!');

            return;
          }
          break;
      }

      $this->comment('Saving new database version');
      $this->settingRepository->setCurrentDatabaseVersion($versionToMigrateTo);

      if (! $this->askBooleanQuestion('Continue with database migration?')) {
        $this->comment('User aborting database migration...');

        return;
      }
    }

    $this->info('Database update finished!');
  }

  private function migrateDatabaseVersionFrom7To8(): bool {
    $this->comment('Running migration from 7 to 8');

    $this->comment('Fixing settings table...');
    try {
      $this->runDbStatement('DELETE FROM settings WHERE `key` = \'community_happy_alert\';');
      $this->runDbStatement('ALTER TABLE settings DROP COLUMN type;');
      $this->runDbStatement('UPDATE settings SET value = \'true\' WHERE value = \'1\';');
      $this->runDbStatement('UPDATE settings SET value = \'false\' WHERE value = \'0\';');
    } catch (Exception $exception) { }

    $this->comment('Fixing user_tokens table...');
    try {
      $this->runDbStatement("UPDATE user_tokens SET token = 'true' WHERE token = '1';");
      $this->runDbStatement("UPDATE user_tokens SET token = 'false' WHERE token = ' ';");
      $this->runDbStatement("UPDATE user_tokens SET token = 'false' WHERE token = '0';");
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Removing movie years table and add maximal tickets');
    try {
      $this->runDbStatement("ALTER TABLE movies DROP FOREIGN KEY movies_movie_year_id_foreign;");
      $this->runDbStatement("ALTER TABLE movies DROP KEY movies_movie_year_id_foreign;");
      $this->runDbStatement("ALTER TABLE movies DROP movie_year_id;");
      $this->runDbStatement("DROP TABLE movie_years;");
      $this->runDbStatement("ALTER TABLE movies ADD maximalTickets INT NOT NULL DEFAULT 20;");
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Adding locations to places table and removing old notify groups table');
    try {
      $this->runDbStatement("ALTER TABLE places ADD location VARCHAR(191);");
      $this->runDbStatement("DROP TABLE place_reservation_notify_groups;");
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 7 to 8 finished!');
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom6To7(): bool {
    $this->comment('Running migration from 6 to 7');

    $this->comment('Altering table dates migrate date from varchar to date');
    try {
      $this->runDbStatement('ALTER TABLE event_dates ADD date_dt DATETIME;');
      $this->runDbStatement('UPDATE event_dates SET date_dt = STR_TO_DATE(event_dates.date, \'%Y-%c-%d %H:%i:%s\');');
      $this->runDbStatement('ALTER TABLE event_dates DROP date, RENAME COLUMN date_dt TO date;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 6 to 7 finished!');
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom5To6(): bool {
    $this->comment('Running migration from 5 to 6');

    $this->comment('Altering table logs adding user id foreign key');
    try {
      $this->runDbStatement('ALTER TABLE logs ADD COLUMN user_id INT UNSIGNED;');
      $this->runDbStatement('ALTER TABLE logs ADD FOREIGN KEY (user_id) REFERENCES `users` (`id`);');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 5 to 6 finished!');

    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom4To5(): bool {
    $this->comment('Running migration from 4 to 5');

    $this->comment('Altering table movies drop worker foreign keys...');
    try {
      $this->runDbStatement('ALTER TABLE movies DROP FOREIGN KEY movies_emergency_worker_id_foreign;');
      $this->runDbStatement('ALTER TABLE movies DROP FOREIGN KEY movies_worker_id_foreign;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table movies add new foreign keys...');
    try {
      $this->runDbStatement('ALTER TABLE movies ADD FOREIGN KEY (emergency_worker_id) REFERENCES `users` (`id`);');
      $this->runDbStatement('ALTER TABLE movies ADD FOREIGN KEY (worker_id) REFERENCES `users` (`id`);');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table broadcasts changing writer foreign key...');
    try {
      $this->runDbStatement('ALTER TABLE broadcasts DROP FOREIGN KEY broadcasts_writer_user_id_foreign;');
      $this->runDbStatement('ALTER TABLE broadcasts ADD FOREIGN KEY (writer_user_id) REFERENCES `users` (`id`);'
      );
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table groups and subgroups adding oderN INT NOT NULL DEFAULT 0');
    try {
      $this->runDbStatement('ALTER TABLE \'groups\' ADD orderN INT NOT NULL DEFAULT 0;');
      $this->runDbStatement('ALTER TABLE subgroups ADD orderN INT NOT NULL DEFAULT 0;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Changing bv_member to varchar(191) and altering data');
    try {
      $this->runDbStatement('ALTER TABLE users MODIFY bv_member VARCHAR (191) NOT NULL;');
      $this->runDbStatement('UPDATE users SET bv_member = \'gemeldet\' where bv_member = \'1\';');
      $this->runDbStatement('UPDATE users SET bv_member = \'\' where bv_member = \'0\';');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 4 to 5 finished!');
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom3To4(): bool {
    $this->comment('Running migration from 3 to 4');

    $this->comment('Altering table user drop member_number');
    try {
      $this->runDbStatement('ALTER TABLE users DROP member_number;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table user add member_number');
    try {
      $this->runDbStatement('ALTER TABLE users ADD member_number VARCHAR(191) DEFAULT null;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 3 to 4 finished!');
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom2To3(): bool {
    $this->comment('Running migration from 2 to 3');

    $this->comment('Deleting jobs table...');
    try {
      $this->runDbStatement('DROP TABLE jobs;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table user add internal_comment');
    try {
      $this->runDbStatement('ALTER TABLE users ADD internal_comment TEXT NULL;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table user add information_denied');
    try {
      $this->runDbStatement('ALTER TABLE users ADD information_denied TINYINT DEFAULT 0 NOT NULL;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table user add member_number');
    try {
      $this->runDbStatement('ALTER TABLE users ADD member_number INTEGER DEFAULT NULL;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Altering table user add bv_member');
    try {
      $this->runDbStatement('ALTER TABLE users ADD bv_member TINYINT DEFAULT 0 NOT NULL;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 2 to 3 finished!');
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom1To2(): bool {
    $this->comment('Running migration from 1 to 2');

    $this->comment('Running events decisions color migrations...');
    try {
      $this->runDbStatement('ALTER TABLE events_decisions ADD color varchar(7) NOT NULL DEFAULT \'#ffffff\';');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running event standard decisions color migrations...');
    try {
      $this->runDbStatement('ALTER TABLE events_standard_decisions ADD color varchar(7) NOT NULL DEFAULT \'#ffffff\';');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 1 to 2 finished!');
    return true;
  }

  /**
   * @return bool
   */
  private function migrateDatabaseVersionFrom0To1(): bool {
    $this->comment('Running migration from 0 to 1');

    $this->comment('Running event startDate, endDate migrations...');
    foreach ($this->eventRepository->getAllEvents() as $event) {
      try {
        $startDate = DB::selectOne('SELECT startDate From events WHERE id = ?', [$event->id])->startDate;
        $endDate = DB::selectOne('SELECT endDate From events WHERE id = ?', [$event->id])->endDate;
      } catch (Exception $exception) {
        return false;
      }

      $this->comment('Event - ' . $event->id . ' | startDate: ' . $startDate . ' | endDate: ' . $endDate);
      $this->eventDateRepository->createEventDate($event, -199, -199, $startDate, null, null);
      $this->eventDateRepository->createEventDate($event, -199, -199, $endDate, null, null);
    }

    $this->comment('Running event startDate, endDate migrations finished!');
    try {
      $this->runDbStatement('ALTER TABLE user_tokens DROP INDEX user_tokens_token_unique;');
      $this->runDbStatement('ALTER TABLE events DROP COLUMN location;');
      $this->runDbStatement('ALTER TABLE events DROP COLUMN startDate;');
      $this->runDbStatement('ALTER TABLE events DROP COLUMN endDate;');
    } catch (Exception $exception) {
      return false;
    }

    $this->comment('Running migration from 0 to 1 finished!');
    return true;
  }

  /**
   * @param string $statement
   * @throws Exception
   */
  private function runDbStatement(string $statement) {
    try {
      DB::statement($statement);
    } catch (Exception $exception) {
      $this->error('Statement failed: "' . $statement . '" | Error message: ' . $exception->getMessage());
      throw new Exception('Migration error...');
    }
  }
}
