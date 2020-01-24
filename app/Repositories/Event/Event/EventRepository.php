<?php


namespace App\Repositories\Event\Event;

use App\Logging;
use App\Models\Events\Event;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class EventRepository implements IEventRepository
{

  protected $eventDateRepository = null;
  protected $eventDecisionRepository = null;

  public function __construct(IEventDateRepository $eventDateRepository, IEventDecisionRepository $eventDecisionRepository) {
    $this->eventDateRepository = $eventDateRepository;
    $this->eventDecisionRepository = $eventDecisionRepository;
  }

  /**
   * @return Event[]|Collection
   */
  public function getAllEvents() {
    return Event::all();
  }

  /**
   * @return Event[]
   */
  public function getAllEventsOrderedByDate() {
    $events = $this->getAllEvents();
    $dates = array();
    foreach ($events as $event) {
      foreach ($this->eventDateRepository->getEventDatesOrderedByDateForEvent($event) as $date) {
        $dates[] = $date;
      }
    }

    usort($dates, function ($a, $b) {
      return strcmp($a->date, $b->date);
    });

    $returnEvents = array();
    foreach ($dates as $date) {
      $add = true;
      foreach ($returnEvents as $returnEvent) {
        if ($date->getEvent()->id == $returnEvent->id) {
          $add = false;
          break;
        }
      }
      if ($add) {
        $returnEvents[] = $date->getEvent();
      }
    }
    foreach ($events as $event) {
      $toAdd = true;
      foreach ($returnEvents as $returnEvent) {
        if ($event->id == $returnEvent->id) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $returnEvents[] = $event;
      }
    }

    return $returnEvents;
  }

  /**
   * @param int $id
   * @return Event
   */
  public function getEventById(int $id) {
    return Event::find($id);
  }

  /**
   * @param string $name
   * @param bool $forEveryone
   * @param string $description
   * @param array $decisions
   * @param array $dates
   * @param Event|null $event
   * @return Event|null
   * @throws Exception
   */
  public function createOrUpdateEvent(string $name, bool $forEveryone, $description, array $decisions, array $dates, Event $event = null) {
    if ($event == null) {
      $event = new Event([
        'name' => $name,
        'forEveryone' => $forEveryone,
        'description' => $description]);

      if (!$event->save()) {
        Logging::error('createOrUpdateEvent', 'Could not create (save) event');
        return null;
      }
    } else {
      $event->name = $name;
      $event->forEveryone = $forEveryone;
      $event->description = $description;

      if (!$event->save()) {
        Logging::error('createOrUpdateEvent', 'Could not update (save) event');
        return null;
      }
    }

    //-------------------------------- Only delete changed decisions --------------------------------------
    $decisionsWhichHaveNotBeenDeleted = array();

    $oldDecisions = $event->eventsDecisions();
    foreach ($oldDecisions as $oldDecision) {
      $toDelete = true;

      foreach ((array)$decisions as $decision) {
        $decisionObject = (object)$decision;
        if ($oldDecision->id == $decisionObject->id) {
          $toDelete = false;
          $decisionsWhichHaveNotBeenDeleted[] = $oldDecision;
          break;
        }
      }

      if ($toDelete) {
        if (!$this->eventDecisionRepository->deleteEventDecision($oldDecision)) {
          Logging::error('createOrUpdateEvent', 'Could not delete old event decision');
          return null;
        }
      }
    }

    foreach ((array)$decisions as $decision) {
      $decisionObject = (object)$decision;
      $toAdd = true;

      foreach ($decisionsWhichHaveNotBeenDeleted as $decisionWhichHaveNotBeenDeleted) {
        if ($decisionObject->id == $decisionWhichHaveNotBeenDeleted->id) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        if ($this->eventDecisionRepository->createEventDecision($event, $decisionObject->decision, $decisionObject->show_in_calendar) == null) {
          $this->deleteEvent($event);
          Logging::error('createOrUpdateEvent', 'Could not add new event decision');
          return null;
        }
      }
    }

    //-------------------------------- Only delete changed dates --------------------------------------
    $datesWhichHaveNotBeenDeleted = array();
    $oldDates = $event->getEventDates();
    foreach ($oldDates as $oldDate) {
      $toDelete = true;

      foreach ((array)$dates as $date) {
        $dateObject = (object)$date;
        if ($oldDate->id == $dateObject->id) {
          $toDelete = false;
          $datesWhichHaveNotBeenDeleted[] = $oldDate;
          break;
        }
      }

      if ($toDelete) {
        if (!$this->eventDateRepository->deleteEventDate($oldDate)) {
          Logging::error('createOrUpdateEvent', 'Could not delete old event date');
          return null;
        }
      }
    }

    foreach ((array)$dates as $date) {
      $dateObject = (object)$date;
      $toAdd = true;

      foreach ($datesWhichHaveNotBeenDeleted as $dateWhichHasNotBeenDeleted) {
        if ($dateObject->id == $dateWhichHasNotBeenDeleted->id) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        if ($this->eventDateRepository->createEventDate($event, $dateObject->x, $dateObject->y, $dateObject->date, $dateObject->location, $dateObject->description) == null) {
          $this->deleteEvent($event);
          Logging::error('createOrUpdateEvent', 'Could not add new event date');
          return null;
        }
      }
    }
    // ----------------------------------------------------------------------------------------------------

    Logging::info('createOrUpdateEvent', 'Successfully created or updated event ' . $event->id);
    return $event;
  }

  /**
   * @param Event $event
   * @return bool
   * @throws Exception
   */
  public function deleteEvent(Event $event) {
    if (!$event->delete()) {
      Logging::error('deleteEvent', 'Could not delete event');
      return false;
    } else {
      return true;
    }
  }

  /**
   * @param Event $event
   * @return stdClass
   */
  public function getReturnable($event) {
    $returnable = new stdClass();

    $startDate = $this->eventDateRepository->getFirstEventDateForEvent($event);
    $endDate = $this->eventDateRepository->getLastEventDateForEvent($event);

    $returnable->id = $event->id;
    $returnable->name = $event->name;
    $returnable->description = $event->description;
    if ($startDate != null) {
      $returnable->start_date = $startDate->date;
    } else {
      $returnable->start_date = null;
    }
    if ($endDate != null) {
      $returnable->end_date = $endDate->date;
    } else {
      $returnable->end_date = null;
    }
    $returnable->for_everyone = $event->forEveryone;

    $decisions = array();
    foreach ($event->eventsDecisions() as $eventsDecision) {
      $decision = new stdClass();
      $decision->id = $eventsDecision->id;
      $decision->decision = $eventsDecision->decision;
      $decision->event_id = $eventsDecision->event_id;
      $decision->show_in_calendar = $eventsDecision->showInCalendar;

      $decisions[] = $decision;
    }
    $returnable->decisions = $decisions;

    $dates = array();
    foreach ($this->eventDateRepository->getEventDatesOrderedByDateForEvent($event) as $eventDate) {
      $date = new stdClass();
      $date->id = $eventDate->id;
      $date->date = $eventDate->date;
      $date->location = $eventDate->location;
      $date->x = $eventDate->x;
      $date->y = $eventDate->y;
      $date->description = $eventDate->description;

      $dates[] = $date;
    }
    $returnable->dates = $dates;


    return $returnable;
  }

  /**
   * @param User $user
   * @return array
   */
  public function getOpenEventsForUser(User $user) {
    $events = array();
    $allEvents = $this->getAllEvents();
    foreach ($allEvents as $event) {
      if ((time() - (60 * 60 * 24)) < strtotime($this->eventDateRepository->getLastEventDateForEvent($event)->date)) {

        $in = false;

        if ($event->forEveryone) {
          $in = true;
        } else {

          foreach ($event->eventsForGroups() as $eventForGroup) {
            foreach ($eventForGroup->group()
                                   ->usersMemberOfGroups() as $userMemberOfGroup) {
              if ($userMemberOfGroup->user_id == $user->id) {
                $in = true;
                break;
              }
            }
          }

          if (!$in) {
            foreach ($event->eventsForSubgroups() as $eventForSubgroup) {
              foreach ($eventForSubgroup->subgroup()
                                        ->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
                if ($userMemberOfSubgroup->user_id == $user->id) {
                  $in = true;
                  break;
                }
              }
            }
          }
        }

        if ($in) {
          $eventUserVotedFor = EventUserVotedForDecision::where('event_id', $event->id)
                                                        ->where('user_id', $user->id)
                                                        ->first();
          $alreadyVoted = ($eventUserVotedFor != null);
          if ($eventUserVotedFor != null) {
            $userDecision = $eventUserVotedFor->decision()->decision;
          } else  {
            $userDecision = null;
          }

          $eventToReturn = $this->getReturnable($event);
          $eventToReturn->already_voted = $alreadyVoted;
          $eventToReturn->user_decision = $userDecision;
          if ($alreadyVoted) {
            $eventToReturn->additional_information = $eventUserVotedFor->additionalInformation;
          }
          $events[] = $eventToReturn;
        }
      }
    }

    return $events;
  }
}