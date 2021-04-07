<?php

namespace App\Console\Commands;

use App\Logging;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use Exception;
use Illuminate\Console\Command;

class DatePollClearUp extends Command {
  protected $signature = 'clear-up {{--force}}';
  protected $description = 'Deletes broadcast attachments which where uploaded but never used / assigned to a broadcast';

  public function __construct(private IBroadcastAttachmentRepository $broadcastAttachmentRepository) {
    parent::__construct();
  }

  /**
   * @throws Exception
   */
  public function handle(): void {
    if (! $this->option('force') && $this->confirm('You sure you want to continue?', false)) {
      return;
    }

    // Clear old unused attachments
    {
      $attachments = $this->broadcastAttachmentRepository->getAttachmentsOlderThanDayWithoutBroadcastId(1);
      foreach ($attachments as $attachment) {
        Logging::info('deleteUnusedBroadcastAttachment', 'Deleting attachment: ' . $attachment->name . ' id - ' . $attachment->id);
        if (! $this->broadcastAttachmentRepository->deleteAttachment($attachment)) {
          Logging::error('deleteUnusedBroadcastAttachment', 'Could not delete attachment: ' . $attachment->name . ' path - ' . $attachment->path);
        }
      }
    }
  }
}
