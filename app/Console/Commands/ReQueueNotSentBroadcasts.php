<?php namespace App\Console\Commands;

use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use Exception;

class ReQueueNotSentBroadcasts extends ACommand {
  protected IBroadcastRepository $broadcastRepository;

  protected $signature = 'requeue-broadcast';
  protected $description = 'Adds not sent broadcasts to queue again. (after accidentally restarting the docker network during email sending)';

  public function __construct(IBroadcastRepository $broadcastRepository) {
    parent::__construct();

    $this->broadcastRepository = $broadcastRepository;
  }

  /**
   * @return void
   * @throws Exception
   */
  public function handle() {
    $broadcastId = (int)$this->askStringQuestion('Please enter a broadcast id', null);

    $broadcast = $this->broadcastRepository->getBroadcastById($broadcastId);
    if ($broadcast == null) {
      $this->error('Broadcast id must be an integer');

      return;
    }

    $this->broadcastRepository->reQueueNotSentBroadcastsForBroadcast($broadcast);
    $this->info('Successfully re queued broadcast');
  }
}
