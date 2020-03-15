<?php


namespace App\Repositories\Event\Event;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\NewEvent;
use App\Models\Events\Event;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\Groups\Group;
use App\Models\User\User;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use App\Repositories\Setting\ISettingRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class EventRepository implements IEventRepository
{

  protected $eventDateRepository = null;
  protected $eventDecisionRepository = null;
  protected $userSettingRepository = null;
  protected $settingRepository = null;

  public function __construct(IEventDateRepository $eventDateRepository, IEventDecisionRepository $eventDecisionRepository, IUserSettingRepository $userSettingRepository, ISettingRepository $settingRepository) {
    $this->eventDateRepository = $eventDateRepository;
    $this->eventDecisionRepository = $eventDecisionRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->settingRepository = $settingRepository;
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
    $creating = false;
    if ($event == null) {
      $creating = true;

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
      $decisionInDatabaseObject = null;

      foreach ($decisionsWhichHaveNotBeenDeleted as $decisionWhichHaveNotBeenDeleted) {
        if ($decisionObject->id == $decisionWhichHaveNotBeenDeleted->id) {
          $decisionInDatabaseObject = $decisionWhichHaveNotBeenDeleted;
          break;
        }
      }

      if ($this->eventDecisionRepository->createOrUpdateEventDecision($event, $decisionObject->decision, $decisionObject->show_in_calendar, $decisionObject->color, $decisionInDatabaseObject) == null) {
        $this->deleteEvent($event);
        Logging::error('createOrUpdateEvent', 'Could not add or update event decision');
        return null;
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

    if ($creating) {
      foreach ($this->getPotentialVotersForEvent($event) as $eventUser) {
        // Directly use User:: methods because in the UserRepository we already use the EventRepository and that would be
        // a circular dependency and RAM will explodes
        $user = User::find($eventUser->id);

        if ($this->userSettingRepository->getNotifyMeOfNewEventsForUser($user)) {
          dispatch(new SendEmailJob(new NewEvent($user->firstname . " " . $user->surname, $event, $this->eventDateRepository, $this->settingRepository), $user->getEmailAddresses()));
        }
      }
    }

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
      $decision->color = $eventsDecision->color;

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
   * @param Event $event
   * @param bool $anonymous
   * @return stdClass
   */
  public function getResultsForEvent(Event $event, bool $anonymous) {
    $results = new stdClass();

    if ($event->forEveryone) {
      $groups = array();

      foreach (Group::all() as $group) {
        $groupToSave = new stdClass();

        $groupToSave->id = $group->id;
        $groupToSave->name = $group->name;

        $usersMemberOfGroup = array();
        foreach ($group->usersMemberOfGroups() as $userMemberOfGroup) {
          $usersMemberOfGroup[] = $this->eventDecisionRepository->getDecisionForUser($userMemberOfGroup->user(), $event, $anonymous);
        }
        $groupToSave->users = $usersMemberOfGroup;

        $subgroups = array();
        foreach ($group->subgroups() as $subgroup) {
          $subgroupToSave = new stdClass();

          $subgroupToSave->id = $subgroup->id;
          $subgroupToSave->name = $subgroup->name;

          $usersMemberOfSubgroup = array();
          foreach ($subgroup->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
            $usersMemberOfSubgroup[] = $this->eventDecisionRepository->getDecisionForUser($userMemberOfSubgroup->user(), $event, $anonymous);
          }

          $subgroupToSave->users = $usersMemberOfSubgroup;

          $subgroups[] = $subgroupToSave;
        }
        $groupToSave->subgroups = $subgroups;
        $groups[] = $groupToSave;
      }

      $results->groups = $groups;

      $all = array();
      // Directly use User:: methods because in the UserRepository we already use the EventRepository and that would be
      // a circular dependency and RAM will explodes
      foreach (User::all() as $user) {
        $all[] = $this->eventDecisionRepository->getDecisionForUser($user, $event, $anonymous);
      }

      $results->allUsers = $all;
    } else {
      $all = array();
      $allSubgroups = array();

      $groups = array();

      $foreachGroups = array();
      foreach ($event->eventsForGroups() as $eventForGroup) {
        $foreachGroups[] = $eventForGroup->group();
      }

      foreach ($foreachGroups as $group) {
        $groupToSave = new stdClass();

        $groupToSave->id = $group->id;
        $groupToSave->name = $group->name;

        $usersMemberOfGroup = array();
        foreach ($group->usersMemberOfGroups() as $userMemberOfGroup) {
          $user = $this->eventDecisionRepository->getDecisionForUser($userMemberOfGroup->user(), $event, $anonymous);
          $usersMemberOfGroup[] = $user;
          if (!in_array($user, $all)) {
            $all[] = $user;
          }
        }
        $groupToSave->users = $usersMemberOfGroup;

        $subgroups = array();
        foreach ($group->subgroups() as $subgroup) {
          $subgroupToSave = new stdClass();

          $subgroupToSave->id = $subgroup->id;
          $subgroupToSave->name = $subgroup->name;
          $subgroupToSave->parent_group_name = $subgroup->group()->name;
          $subgroupToSave->parent_group_id = $subgroup->group_id;

          $usersMemberOfSubgroup = array();
          foreach ($subgroup->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
            $user = $this->eventDecisionRepository->getDecisionForUser($userMemberOfSubgroup->user(), $event, $anonymous);
            $usersMemberOfSubgroup[] = $user;
            if (!in_array($user, $all)) {
              $all[] = $user;
            }
          }

          $subgroupToSave->users = $usersMemberOfSubgroup;

          $subgroups[] = $subgroupToSave;
          $allSubgroups[] = $subgroupToSave;
        }
        $groupToSave->subgroups = $subgroups;
        $groups[] = $groupToSave;
      }

      $unknownGroupToSave = new stdClass();

      $unknownGroupToSave->id = -1;
      $unknownGroupToSave->name = "unknown";
      $unknownGroupToSave->users = array();

      $subgroups = array();
      foreach ($event->eventsForSubgroups() as $eventForSubgroup) {
        $subgroupToSave = new stdClass();

        $subgroup = $eventForSubgroup->subgroup();
        $subgroupToSave->id = $subgroup->id;
        $subgroupToSave->name = $subgroup->name;
        $subgroupToSave->parent_group_name = $subgroup->group()->name;
        $subgroupToSave->parent_group_id = $subgroup->group_id;

        $usersMemberOfSubgroup = array();
        foreach ($subgroup->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
          $user = $this->eventDecisionRepository->getDecisionForUser($userMemberOfSubgroup->user(), $event, $anonymous);
          $usersMemberOfSubgroup[] = $user;
          if (!in_array($user, $all)) {
            $all[] = $user;
          }
        }

        $subgroupToSave->users = $usersMemberOfSubgroup;

        if (!in_array($subgroupToSave, $allSubgroups)) {
          $subgroups[] = $subgroupToSave;
        }
      }
      $unknownGroupToSave->subgroups = $subgroups;
      $groups[] = $unknownGroupToSave;

      $results->groups = $groups;

      $results->allUsers = $all;
    }
    $results->anonymous = $anonymous;
    return $results;
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
            $userDecision = new stdClass();
            $userDecision->id = $eventUserVotedFor->decision()->id;
            $userDecision->event_id = $eventUserVotedFor->decision()->event_id;
            $userDecision->show_in_calendar = $eventUserVotedFor->decision()->showInCalendar;
            $userDecision->color = $eventUserVotedFor->decision()->color;
            $userDecision->created_at = $eventUserVotedFor->decision()->created_at;
            $userDecision->updated_at = $eventUserVotedFor->decision()->updated_at;
            $userDecision->additional_information = $eventUserVotedFor->additionalInformation;
          } else {
            $userDecision = null;
          }

          $eventToReturn = $this->getReturnable($event);
          $eventToReturn->already_voted = $alreadyVoted;
          $eventToReturn->user_decision = $userDecision;
          $events[] = $eventToReturn;
        }
      }
    }

    return $events;
  }

  /**
   * @param Event $event
   * @return array
   */
  public function getPotentialVotersForEvent(Event $event) {
    return $this->getResultsForEvent($event, false)->allUsers;
  }
}