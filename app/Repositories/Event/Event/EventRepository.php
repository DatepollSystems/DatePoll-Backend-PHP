<?php


namespace App\Repositories\Event\Event;

use App\Logging;
use App\Models\Events\Event;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;

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
      foreach ($this->eventDateRepository->getEventDatesForEvent($event) as $date) {
        $dates[] = $date;
      }
    }

    usort($dates, function ($a, $b) {
      return strcmp($a->date, $b->date);
    });

    $returnEvents = array();
    foreach ($dates as $date) {
      $returnEvents[] = $date->getEvent();
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
  public function createOrUpdateEvent(string $name, bool $forEveryone, string $description, array $decisions, array $dates, Event $event = null) {
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
        if (!$this->eventDecisionRepository->deleteEventDecision($this->eventDecisionRepository->getEventDecisionById($oldDecision->id))) {
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
        if ($this->eventDecisionRepository->createEventDecision($event, $decisionObject->decision, $decisionObject->showInCalendar) != null) {
          $event->delete();
          Logging::error('createOrUpdateEvent', 'Could not add new event decision');
          return null;
        }
      }
    }
    // ----------------------------------------------------------------------------------------------------


    //TODO: Event dates saving

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
}