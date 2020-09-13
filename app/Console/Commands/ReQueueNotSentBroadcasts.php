<?php namespace App\Console\Commands;

use App\Models\User\User;
use App\Models\User\UserPermission;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use Illuminate\Console\Command;

class ReQueueNotSentBroadcasts extends ACommand
{
  protected $broadcastRepository = null;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'requeue-broadcast';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Adds not sent broadcasts to queue again. (after accidentally restarting the docker network during email sending)';

  /**
   * Create a new command instance.
   *
   * @param IBroadcastRepository $broadcastRepository
   */
  public function __construct(IBroadcastRepository $broadcastRepository) {
    parent::__construct();

    $this->broadcastRepository = $broadcastRepository;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   * @throws \Exception
   */
  public function handle() {
    $broadcastId = (int)$this->askStringQuestion('Please enter a broadcast id', null);

    $broadcast = $this->broadcastRepository->getBroadcastById($broadcastId);
    if ($broadcast == null) {
      $this->error('Broadcast id must be an integer');
      return;
    }

    $this->broadcastRepository->reQueueNotSentBroadcastsForBroadcast($broadcast);
    $this->comment('Successfully requeued broadcast');
  }
}
