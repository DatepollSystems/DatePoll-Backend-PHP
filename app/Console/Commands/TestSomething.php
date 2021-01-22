<?php namespace App\Console\Commands;

use App\Utils\StringHelper;
use Illuminate\Console\Command;

class TestSomething extends Command {
  protected $signature = 'test-something';
  protected $description = 'Tests something';

  public function __construct() {
    parent::__construct();
  }

  public function handle() {
    $test = '';
    if (StringHelper::nullAndEmpty($test)) {
      $this->line('null and empty');
    } else {
      $this->line('not null and empty');
    }
  }
}
