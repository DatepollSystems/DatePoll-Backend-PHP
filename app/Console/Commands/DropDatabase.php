<?php namespace App\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;

class DropDatabase extends Command
{
  protected $signature = 'drop-database';
  protected $description = 'Drops complete database';

  public function __construct()
  {
    parent::__construct();
  }

  public function handle()
  {
    if (!$this->confirm('You sure you want to continue?', false)) {
      $this->comment('Aborting...');
      return;
    }

    Schema::dropAllTables();

    $this->info("Database dropped successfully");
  }
}
