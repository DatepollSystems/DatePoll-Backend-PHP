<?php

namespace App\Repositories\Event\Event;

use App\Jobs\CreateNewEventEmailsJob;
use App\Logging;
use App\Models\Events\Event;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\ArrayHelper;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use stdClass;

class EventRepository implements IEventRepository {
  protected IEventDateRepository $eventDateRepository;
  protected IEventDecisionRepository $eventDecisionRepository;
  protected IUserSettingRepository $userSettingRepository;
  protected ISettingRepository $settingRepository;
  protected IGroupRepository $groupRepository;

  public function __construct(
    IEventDateRepository $eventDateRepository,
    IEventDecisionRepository $eventDecisionRepository,
    IUserSettingRepository $userSettingRepository,
    ISettingRepository $settingRepository,
    IGroupRepository $groupRepository
  ) {
    $this->eventDateRepository = $eventDateRepository;
    $this->eventDecisionRepository = $eventDecisionRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->settingRepository = $settingRepository;
    $this->groupRepository = $groupRepository;
  }

  /**
   * @return Event[]|Collection
   */
  public function getAllEvents() {
    return Event::all();
  }

  /**
   * @return int[]
   */
  public function getYearsOfEvents(): array {
    return ArrayHelper::getPropertyArrayOfObjectArray(
      DB::table('event_dates')->orderBy('date')->selectRaw('YEAR(date) as year')->get()->unique()->values()->toArray(),
      'year'
    );
  }

  /**
   * @param int|null $year
   * @return Event[]
   */
  public function getEventsOrderedByDate(int $year = null) {
    $query = DB::table('event_dates');
    if ($year != null) {
      $query = $query->whereYear('date', '=', $year);
    }

    return Event::find(ArrayHelper::getPropertyArrayOfObjectArray(
      $query->orderBy('date')->addSelect('event_id')->get()->toArray(),
      'event_id'
    ));
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
  public function createOrUpdateEvent(
    string $name,
    bool $forEveryone,
    string $description,
    array $decisions,
    array $dates,
    Event $event = null
  ) {
    $creating = false;
    if ($event == null) {
      $creating = true;

      $event = new Event([
        'name' => $name,
        'forEveryone' => $forEveryone,
        'description' => $description,]);

      if (! $event->save()) {
        Logging::error('createOrUpdateEvent', 'Could not create (save) event');

        return null;
      }
    } else {
      $event->name = $name;
      $event->forEveryone = $forEveryone;
      $event->description = $description;

      if (! $event->save()) {
        Logging::error('createOrUpdateEvent', 'Could not update (save) event');

        return null;
      }
    }

    //-------------------------------- Only delete changed decisions --------------------------------------
    $decisionsWhichHaveNotBeenDeleted = [];

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
        if (! $this->eventDecisionRepository->deleteEventDecision($oldDecision)) {
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

      if ($this->eventDecisionRepository->createOrUpdateEventDecision(
        $event,
        $decisionObject->decision,
        $decisionObject->show_in_calendar,
        $decisionObject->color,
        $decisionInDatabaseObject
      ) == null) {
        $this->deleteEvent($event);
        Logging::error('createOrUpdateEvent', 'Could not add or update event decision');

        return null;
      }
    }

    //-------------------------------- Only delete changed dates --------------------------------------
    $datesWhichHaveNotBeenDeleted = [];
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
        if (! $this->eventDateRepository->deleteEventDate($oldDate)) {
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
        if ($this->eventDateRepository->createEventDate(
          $event,
          $dateObject->x,
          $dateObject->y,
          $dateObject->date,
          $dateObject->location,
          $dateObject->description
        ) == null) {
          $this->deleteEvent($event);
          Logging::error('createOrUpdateEvent', 'Could not add new event date');

          return null;
        }
      }
    }
    // ----------------------------------------------------------------------------------------------------

    Logging::info('createOrUpdateEvent', 'Successfully created or updated event ' . $event->id);

    if ($creating) {
      $time = new DateTime();
      $time->add(new DateInterval('PT' . 1 . 'M'));
      Queue::later(
        $time,
        new CreateNewEventEmailsJob(
          $event,
          $this,
          $this->eventDateRepository,
          $this->userSettingRepository,
          $this->settingRepository
        ),
        null,
        'high'
      );
    }

    return $event;
  }

  /**
   * @param Event $event
   * @return bool
   * @throws Exception
   */
  public function deleteEvent(Event $event) {
    if (! $event->delete()) {
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
  public function getReturnable(Event $event) {
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

    $decisions = [];
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

    $dates = [];
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
      $groups = [];

      foreach ($this->groupRepository->getAllGroupsOrdered() as $group) {
        $groupToSave = new stdClass();

        $groupToSave->id = $group->id;
        $groupToSave->name = $group->name;

        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $userMemberOfGroup) {
          $groupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser(
            $userMemberOfGroup,
            $event,
            $anonymous
          );
        }
        $groupToSave->users = $groupResultUsers;

        $subgroups = [];
        foreach ($group->getSubgroupsOrdered() as $subgroup) {
          $subgroupToSave = new stdClass();

          $subgroupToSave->id = $subgroup->id;
          $subgroupToSave->name = $subgroup->name;

          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $subgroupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser($sUser, $event, $anonymous);
          }

          $subgroupToSave->users = $subgroupResultUsers;

          $subgroups[] = $subgroupToSave;
        }
        $groupToSave->subgroups = $subgroups;
        $groups[] = $groupToSave;
      }

      $results->groups = $groups;

      $allUsers = [];
      // Directly use User:: methods because in the UserRepository we already use the EventRepository and that would be
      // a circular dependency and RAM will explodes
      foreach (User::orderBy('surname')
        ->get() as $user) {
        $allUsers[] = $this->eventDecisionRepository->getDecisionForUser($user, $event, $anonymous);
      }

      $results->allUsers = $allUsers;
    } else {
      $allUsers = [];
      $allUserIds = [];
      $allSubgroups = [];

      $groups = [];

      foreach ($event->getGroupsOrdered() as $group) {
        $groupToSave = new stdClass();

        $groupToSave->id = $group->id;
        $groupToSave->name = $group->name;

        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $gUser) {
          $user = $this->eventDecisionRepository->getDecisionForUser($gUser, $event, $anonymous);
          $groupResultUsers[] = $user;
          if (! ArrayHelper::inArray($allUserIds, $gUser->id)) {
            $allUsers[] = $user;
            $allUserIds[] = $gUser->id;
          }
        }
        $groupToSave->users = $groupResultUsers;

        $subgroups = [];
        foreach ($group->getSubgroupsOrdered() as $subgroup) {
          $subgroupToSave = new stdClass();

          $subgroupToSave->id = $subgroup->id;
          $subgroupToSave->name = $subgroup->name;
          $subgroupToSave->parent_group_name = $subgroup->group()->name;
          $subgroupToSave->parent_group_id = $subgroup->group_id;

          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $user = $this->eventDecisionRepository->getDecisionForUser($sUser, $event, $anonymous);
            $subgroupResultUsers[] = $user;
            if (! ArrayHelper::inArray($allUserIds, $sUser->id)) {
              $allUsers[] = $user;
              $allUserIds[] = $sUser->id;
            }
          }

          $subgroupToSave->users = $subgroupResultUsers;

          $subgroups[] = $subgroupToSave;
          $allSubgroups[] = $subgroupToSave;
        }
        $groupToSave->subgroups = $subgroups;
        $groups[] = $groupToSave;
      }

      $unknownGroupToSave = new stdClass();

      $unknownGroupToSave->id = -1;
      $unknownGroupToSave->name = 'unknown';
      $unknownGroupToSave->users = [];

      $subgroups = [];
      foreach ($event->getSubgroupsOrdered() as $subgroup) {
        $subgroupToSave = new stdClass();

        $subgroupToSave->id = $subgroup->id;
        $subgroupToSave->name = $subgroup->name;
        $subgroupToSave->parent_group_name = $subgroup->group()->name;
        $subgroupToSave->parent_group_id = $subgroup->group_id;

        $subgroupResultUsers = [];
        foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
          $user = $this->eventDecisionRepository->getDecisionForUser($sUser, $event, $anonymous);
          $subgroupResultUsers[] = $user;
          if (! in_array($sUser->id, $allUserIds)) {
            $allUsers[] = $user;
            $allUserIds[] = $sUser->id;
          }
        }

        $subgroupToSave->users = $subgroupResultUsers;

        if (! in_array($subgroupToSave, $allSubgroups)) {
          $subgroups[] = $subgroupToSave;
        }
      }
      $unknownGroupToSave->subgroups = $subgroups;
      $groups[] = $unknownGroupToSave;

      $results->groups = $groups;

      $results->allUsers = $allUsers;
    }
    $results->anonymous = $anonymous;

    return $results;
  }

  /**
   * @param User $user
   * @return array
   */
  public function getOpenEventsForUser(User $user) {
    $events = [];

    $date = date('Y-m-d H:i:s');
    $eventIdsResult = DB::table('event_dates')->where(
      'event_dates.date',
      '>',
      $date
    )->orderBy('event_dates.date')->addSelect('event_dates.event_id as id')->get()->unique('id')->all();
    foreach (ArrayHelper::getPropertyArrayOfObjectArray($eventIdsResult, 'id') as $eventId) {
      $event = $this->getEventById($eventId);

      $inGroup = DB::table('events_for_groups')->join(
        'users_member_of_groups',
        'events_for_groups.group_id',
        '=',
        'users_member_of_groups.group_id'
      )->where(
          'events_for_groups.event_id',
          '=',
          $event->id
        )->where('users_member_of_groups.user_id', '=', $user->id)->count() > 0;

      $inSubgroup = DB::table('events_for_subgroups')->join(
        'users_member_of_subgroups',
        'events_for_subgroups.subgroup_id',
        '=',
        'users_member_of_subgroups.subgroup_id'
      )->where(
          'events_for_subgroups.event_id',
          '=',
          $event->id
        )->where('users_member_of_subgroups.user_id', '=', $user->id)->count() > 0;

      if ($event->forEveryone || $inGroup || $inSubgroup) {
        $returnableEvent = $this->createOpenEventReturnable($event, $user);
        $events[] = $returnableEvent;
      }
    }

    return $events;
  }

  /**
   * @param Event $event
   * @param User $user
   * @return stdClass
   */
  private function createOpenEventReturnable(Event $event, User $user) {
    $eventUserVotedFor = $this->eventDecisionRepository->getEventUserVotedForDecisionByEventIdAndUserId(
      $event->id,
      $user->id
    );
    $alreadyVoted = ($eventUserVotedFor != null);

    $eventToReturn = $this->getReturnable($event);
    $eventToReturn->already_voted = $alreadyVoted;
    $eventToReturn->user_decision = $this->getUserDecisionReturnable($eventUserVotedFor);

    return $eventToReturn;
  }

  /**
   * @param Event $event
   * @return array
   */
  public function getPotentialVotersForEvent(Event $event) {
    return $this->getResultsForEvent($event, false)->allUsers;
  }

  /**
   * @param EventUserVotedForDecision|null $eventUserVotedForDecision
   * @return stdClass|null
   */
  public function getUserDecisionReturnable(?EventUserVotedForDecision $eventUserVotedForDecision) {
    $userDecision = null;
    if ($eventUserVotedForDecision != null) {
      $decision = $eventUserVotedForDecision->decision();
      $userDecision = new stdClass();
      $userDecision->id = $decision->id;
      $userDecision->decision = $decision->decision;
      $userDecision->event_id = $decision->event_id;
      $userDecision->show_in_calendar = $decision->showInCalendar;
      $userDecision->color = $decision->color;
      $userDecision->created_at = $decision->created_at;
      $userDecision->updated_at = $decision->updated_at;
      $userDecision->additional_information = $eventUserVotedForDecision->additionalInformation;
    }

    return $userDecision;
  }
}
