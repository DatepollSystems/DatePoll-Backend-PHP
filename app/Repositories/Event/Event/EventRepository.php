<?php

namespace App\Repositories\Event\Event;

use App\Jobs\CreateNewEventEmailsJob;
use App\Logging;
use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\User\User;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\ArrayHelper;
use App\Utils\DateHelper;
use App\Utils\QueueHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use stdClass;

class EventRepository implements IEventRepository {
  public function __construct(
    protected IUserRepository $userRepository,
    protected IEventDateRepository $eventDateRepository,
    protected IEventDecisionRepository $eventDecisionRepository,
    protected IUserSettingRepository $userSettingRepository,
    protected ISettingRepository $settingRepository,
    protected IGroupRepository $groupRepository
  ) {
  }

  /**
   * @return Event[]
   */
  public function getAllEvents(): array {
    return Event::all()->all();
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
  public function getEventsOrderedByDate(int $year = null): array {
    $query = DB::table('event_dates');
    if ($year != null) {
      $query = $query->whereYear('date', '=', $year);
    }

    $events = [];

    $eventIdsResult = $query->orderBy('date')->get(['event_id', 'date'])->unique('event_id')->all();
    // DO NOT EVER use Event::find(Array) because it messes with the orderBy
    foreach (ArrayHelper::getPropertyArrayOfObjectArray($eventIdsResult, 'event_id') as $eventId) {
      $events[] = $this->getEventById($eventId);
    }

    return $events;
  }

  /**
   * @param int $id
   * @return Event|null
   */
  public function getEventById(int $id): ?Event {
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
  ): ?Event {
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

    foreach ($event->eventDecisions as $oldDecision) {
      $toDelete = true;

      foreach ($decisions as $decision) {
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

    foreach ($decisions as $decision) {
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
    foreach ($event->eventDates as $oldDate) {
      $toDelete = true;

      foreach ($dates as $date) {
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

    foreach ($dates as $date) {
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
      $time = DateHelper::getCurrentDateTime();
      $time = DateHelper::addMinuteToDateTime($time, 1);
      QueueHelper::addDelayedJobToHighQueue(new CreateNewEventEmailsJob(
        $event,
        $this,
        $this->userSettingRepository,
        $this->settingRepository
      ), $time);
    }

    return $event;
  }

  /**
   * @param Event $event
   * @return bool
   * @throws Exception
   */
  public function deleteEvent(Event $event): bool {
    if (! $event->delete()) {
      Logging::error('deleteEvent', 'Could not delete event');

      return false;
    }

    return true;
  }

  /**
   * @param User $user
   * @return array
   */
  public function getOpenEventsForUser(User $user): array {
    $events = [];

    $date = date('Y-m-d H:i:s');
    $eventIdsResult = DB::table('event_dates')->where(
      'date',
      '>',
      $date)->orderBy('date')->get(['event_id', 'date'])->unique('event_id')->all();
    // DO NOT EVER use Event::find(Array) because it messes with the orderBy
    foreach (ArrayHelper::getPropertyArrayOfObjectArray($eventIdsResult, 'event_id') as $eventId) {
      $event = $this->getEventById($eventId);

      $inGroup = true;
      $inSubgroup = true;
      if (! $event->forEveryone) {
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
      }

      if ($event->forEveryone || $inGroup || $inSubgroup) {
        $events[] = $event->toArrayWithUserDecisionByUserId($user->id);
      }
    }

    return $events;
  }

  /**
   * @param Event $event
   * @param bool $anonymous
   * @param bool $calculateCharts
   * @return array
   */
  public function getResultsForEvent(Event $event, bool $anonymous, bool $calculateCharts = true): array {
    $eventDecisions = $this->eventDecisionRepository->getEventDecisionsByEventId($event->id);
    $results = [];
    $results['anonymous'] = $anonymous;

    if ($event->forEveryone) {
      $groups = [];

      foreach ($this->groupRepository->getAllGroupsOrdered() as $group) {
        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $user) {
          $groupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser(
            $user,
            $event);
        }

        $subgroups = [];
        foreach ($group->getSubgroupsOrdered() as $subgroup) {
          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $subgroupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser($sUser, $event);
          }

          if ($calculateCharts) {
            $chart = $this->calculateChart($eventDecisions, $subgroupResultUsers);
          } else {
            $chart = null;
          }
          /** @noinspection DisconnectedForeachInstructionInspection */
          if ($anonymous) {
            $subgroupResultUsers = null;
          }

          $subgroups[] = ['id' => $subgroup->id, 'name' => $subgroup->name, 'users' => $subgroupResultUsers,
                          'chart' => $chart,
                          'parent_group_name' => $subgroup->getGroup()->name, 'parent_group_id' => $subgroup->group_id];
        }

        if ($calculateCharts) {
          $chart = $this->calculateChart($eventDecisions, $groupResultUsers);
        } else {
          $chart = null;
        }
        /** @noinspection DisconnectedForeachInstructionInspection */
        if ($anonymous) {
          $groupResultUsers = null;
        }

        $groups[] = ['id' => $group->id, 'name' => $group->name, 'users' => $groupResultUsers, 'chart' => $chart,
                     'subgroups' => $subgroups];
      }

      $results['groups'] = $groups;

      $allUsers = [];
      foreach ($this->userRepository->getAllUsersOrderedBySurname() as $user) {
        $allUsers[] = $this->eventDecisionRepository->getDecisionForUser($user, $event);
      }

    } else {
      $allUsers = [];
      $allUserIds = [];
      $allSubgroupsIds = [];

      $groups = [];

      foreach ($event->getGroupsOrdered() as $group) {
        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $gUser) {
          $user = $this->eventDecisionRepository->getDecisionForUser($gUser, $event);
          $groupResultUsers[] = $user;
          if (! ArrayHelper::inArray($allUserIds, $gUser->id)) {
            $allUsers[] = $user;
            $allUserIds[] = $gUser->id;
          }
        }

        $subgroups = [];
        foreach ($group->getSubgroupsOrdered() as $subgroup) {
          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $user = $this->eventDecisionRepository->getDecisionForUser($sUser, $event);
            $subgroupResultUsers[] = $user;
            if (ArrayHelper::notInArray($allUserIds, $sUser->id)) {
              $allUsers[] = $user;
              $allUserIds[] = $sUser->id;
            }
          }

          if ($calculateCharts) {
            $chart = $this->calculateChart($eventDecisions, $subgroupResultUsers);
          } else {
            $chart = null;
          }
          /** @noinspection DisconnectedForeachInstructionInspection */
          if ($anonymous) {
            $subgroupResultUsers = null;
          }

          $subgroupToSave = ['id' => $subgroup->id, 'name' => $subgroup->name,
                             'parent_group_name' => $subgroup->getGroup()->name,
                             'parent_group_id' => $subgroup->group_id,
                             'users' => $subgroupResultUsers, 'chart' => $chart];
          $subgroups[] = $subgroupToSave;
          $allSubgroupsIds[] = $subgroup->id;
        }
        if ($calculateCharts) {
          $chart = $this->calculateChart($eventDecisions, $groupResultUsers);
        } else {
          $chart = null;
        }
        /** @noinspection DisconnectedForeachInstructionInspection */
        if ($anonymous) {
          $groupResultUsers = null;
        }

        $groups[] = ['id' => $group->id, 'name' => $group->name, 'users' => $groupResultUsers, 'chart' => $chart,
                     'subgroups' => $subgroups];
      }

      $subgroups = [];
      foreach ($event->getSubgroupsOrdered() as $subgroup) {
        if (ArrayHelper::notInArray($allSubgroupsIds, $subgroup->id)) {
          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $user = $this->eventDecisionRepository->getDecisionForUser($sUser, $event);
            $subgroupResultUsers[] = $user;
            if (ArrayHelper::notInArray($allUserIds, $sUser->id)) {
              $allUsers[] = $user;
              $allUserIds[] = $sUser->id;
            }
          }

          if ($calculateCharts) {
            $chart = $this->calculateChart($eventDecisions, $subgroupResultUsers);
          } else {
            $chart = null;
          }
          if ($anonymous) {
            $subgroupResultUsers = null;
          }

          $subgroups[] = ['id' => $subgroup->id, 'name' => $subgroup->name,
                          'parent_group_name' => $subgroup->getGroup()->name, 'parent_group_id' => $subgroup->group_id,
                          'users' => $subgroupResultUsers, 'chart' => $chart];
        }
      }
      // Add unknown group with single subgroups
      $groups[] = ['id' => -1, 'name' => 'unknown', 'users' => [], 'subgroups' => $subgroups];

      $results['groups'] = $groups;
    }

    $results['allUsers'] = $allUsers;

    if ($anonymous) {
      $results['allUsers'] = null;
    }

    if ($calculateCharts) {
      $results['chart'] = $this->calculateChart($eventDecisions, $allUsers);
    }

    return $results;
  }

  /**
   * @param Event $event
   * @return array
   */
  public function getPotentialVotersForEvent(Event $event): array {
    return $this->getResultsForEvent($event, false, false)['allUsers'];
  }

  /**
   * @param EventDecision[] $eventDecisions
   * @param array $userDecisions
   * @return array
   */
  private function calculateChart(array $eventDecisions, array $userDecisions): array {
    $objects = [];

    foreach ($eventDecisions as $decision) {
      $object = new stdClass();
      $object->id = $decision->id;
      $object->name = $decision->decision;
      $object->color = $decision->color;
      $object->count = 0;

      $objects[] = $object;
    }

    $votedUsersCount = 0;
    foreach ($userDecisions as $userDecision) {
      foreach ($objects as $object) {
        if ($userDecision['decisionId'] == $object->id) {
          $object->count++;
          $votedUsersCount++;
          break;
        }
      }
    }

    $chartElements = [];
    if ($votedUsersCount > 0) {
      foreach ($objects as $object) {
        $chartElements[] = ['name' => $object->name,
                            'percentWidth' => floor(($object->count / $votedUsersCount) * 100),
                            'count' => $object->count,
                            'color' => $object->color];
      }
    }
    return $chartElements;
  }
}
