<?php

namespace App\Console\Commands;

use App\Logging;
use App\Models\User\UserCode;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Utils\DateHelper;
use Exception;
use Illuminate\Console\Command;

class DatePollCleanUp extends Command {
  protected $signature = 'clean-up {{--force}}';
  protected $description = 'Deletes unused broadcast attachments and user codes';

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
        Logging::info(
          'deleteUnusedBroadcastAttachment',
          'Deleting attachment: ' . $attachment->name . ' id - ' . $attachment->id
        );
        if (! $this->broadcastAttachmentRepository->deleteAttachment($attachment)) {
          Logging::error(
            'deleteUnusedBroadcastAttachment',
            'Could not delete attachment: ' . $attachment->name . ' path - ' . $attachment->path
          );
        }
      }
    }

    // Clear old user codes
    {
      $userCodes = UserCode::where(
        'created_at',
        '<',
        DateHelper::removeDayFromDateFormatted(DateHelper::getCurrentDateFormatted(), 10)
      )->get();
      foreach ($userCodes as $userCode) {
        Logging::info(
          'deleteOldUserCodes',
          'Deleting code id: ' . $userCode->id . '; user id: ' . $userCode->user_id . '; rate limit: ' .
          $userCode->rate_limit . '; created at: ' . $userCode->created_at
        );
        $userCode->delete();
      }
    }
  }
}
