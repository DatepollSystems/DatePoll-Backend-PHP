<?php

namespace App\Mail;

use App\Models\Events\Event;
use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

/**
 * Class NewEvent
 * @package App\Mail
 * @property string $name
 * @property string $startDate
 * @property string $endDate
 * @property string $eventName
 * @property int $eventId
 * @property string $DatePollAddress
 */
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
   * @param ISettingRepository $settingRepository
   */
  public function __construct(string $name, Event $event, ISettingRepository $settingRepository) {
    parent::__construct('newEvent');

    $this->DatePollAddress = $settingRepository->getUrl();
    $this->startDate = $event->getFirstEventDate()->date;
    $this->endDate = $event->getLastEventDate()->date;
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
