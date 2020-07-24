<?php

namespace App\Jobs;

use App\Mail\NewEvent;
use App\Models\Events\Event;
use App\Models\User\User;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @property Event $event
 * @property IEventRepository $eventRepository
 * @property IEventDateRepository $eventDateRepository
 * @property IUserSettingRepository $userSettingRepository
 * @property ISettingRepository $settingRepository
 */
class CreateNewEventEmailsJob extends Job
{

  private $event;
  private $eventRepository;
  private $eventDateRepository;
  private $userSettingRepository;
  private $settingRepository;

  /**
   * Create a new job instance.
   *
   * @param Event $event
   * @param IEventRepository $eventRepository
   * @param IEventDateRepository $eventDateRepository
   * @param IUserSettingRepository $userSettingRepository
   * @param ISettingRepository $settingRepository
   */
  public function __construct($event, IEventRepository $eventRepository, IEventDateRepository $eventDateRepository,
                              IUserSettingRepository $userSettingRepository, ISettingRepository $settingRepository) {
    $this->event = $event;
    $this->eventRepository = $eventRepository;
    $this->eventDateRepository = $eventDateRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->settingRepository = $settingRepository;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle() {
    foreach ($this->eventRepository->getPotentialVotersForEvent($this->event) as $eventUser) {
      // Directly use User:: methods because in the UserRepository we already use the EventRepository and that would be
      // a circular dependency and RAM will explode
      // Also check if user is not information denied
      $user = User::find($eventUser->id);

      if ($this->userSettingRepository->getNotifyMeOfNewEventsForUser($user) && !$user->information_denied && $user->activated) {
        dispatch(new SendEmailJob(new NewEvent($user->firstname, $this->event, $this->eventDateRepository, $this->settingRepository), $user->getEmailAddresses()))->onQueue('default');
      }
    }
  }
}
