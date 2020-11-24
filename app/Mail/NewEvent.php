<?php

namespace App\Mail;

use App\Models\Events\Event;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class NewEvent extends ADatePollMailable {
  use Queueable, SerializesModels;

  public string $name;
  public string $startDate;
  public string $endDate;
  public string $eventName;
  public int $eventId;
  public string $DatePollAddress;

  /**
   * Create a new message instance.
   *
   * @param string $name
   * @param Event $event
   * @param IEventDateRepository $eventDateRepository
   * @param ISettingRepository $settingRepository
   */
  public function __construct($name, Event $event, IEventDateRepository $eventDateRepository, ISettingRepository $settingRepository) {
    parent::__construct('newEvent');

    $this->DatePollAddress = $settingRepository->getUrl();
    $this->startDate = $eventDateRepository->getFirstEventDateForEvent($event)->date;
    $this->endDate = $eventDateRepository->getLastEventDateForEvent($event)->date;
    $this->name = $name;
    $this->eventName = $event->name;
    $this->eventId = $event->id;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {
    return $this
      ->subject('» Neues Event erstellt')
      ->view('emails.newEvent.newEvent')
      ->text('emails.newEvent.newEvent_plain');
  }
}
