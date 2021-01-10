<?php

namespace App\Jobs;

use App\Mail\NewEvent;
use App\Models\Events\Event;
use App\Models\User\User;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\MailHelper;
use DateInterval;
use DateTime;
use Exception;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @property Event $event
 * @property IEventRepository $eventRepository
 * @property IEventDateRepository $eventDateRepository
 * @property IUserSettingRepository $userSettingRepository
 * @property ISettingRepository $settingRepository
 */
class CreateNewEventEmailsJob extends Job {
  /**
   * @param Event $event
   * @param IEventRepository $eventRepository
   * @param IUserSettingRepository $userSettingRepository
   * @param ISettingRepository $settingRepository
   */
  public function __construct(
    private Event $event,
    private IEventRepository $eventRepository,
    private IUserSettingRepository $userSettingRepository,
    private ISettingRepository $settingRepository
  ) {  }

  /**
   * Execute the job.
   *
   * @return void
   * @throws Exception
   */
  public function handle() {
    $time = new DateTime();
    foreach ($this->eventRepository->getPotentialVotersForEvent($this->event) as $eventUser) {
      // Directly use User:: methods because in the UserRepository we already use the EventRepository and that would be
      // a circular dependency and RAM will explode
      // Also check if user is not information denied
      $user = User::find($eventUser->id);

      if ($this->userSettingRepository->getNotifyMeOfNewEventsForUser($user) && ! $user->information_denied && $user->activated) {
        $time->add(new DateInterval('PT' . 1 . 'M'));
        MailHelper::sendDelayedEmailOnLowQueue(new NewEvent(
          $user->firstname,
          $this->event,
          $this->settingRepository
        ), $user->getEmailAddresses(), $time);
      }
    }
  }
}
