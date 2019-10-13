<?php namespace App\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Command;

class DropTables extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'drop-all-tables';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Drops all tables';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    Schema::dropAllTables();

    $this->comment(PHP_EOL."If no errors showed up, all tables were dropped successfully".PHP_EOL);

  }
}