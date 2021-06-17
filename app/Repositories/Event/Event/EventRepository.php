<?php /** @noinspection DisconnectedForeachInstructionInspection */

namespace App\Repositories\Event\Event;

use App\Jobs\CreateNewEventEmailsJob;
use App\Logging;
use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\Events\EventLinkedBroadcast;
use App\Models\User\User;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\Interfaces\AHasYearsRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\ArrayHelper;
use App\Utils\DateHelper;
use App\Utils\QueueHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\Pure;
use stdClass;

class EventRepository extends AHasYearsRepository implements IEventRepository {
  #[Pure]
  public function __construct(
    protected IUserRepository $userRepository,
    protected IEventDateRepository $eventDateRepository,
    protected IEventDecisionRepository $eventDecisionRepository,
    protected IUserSettingRepository $userSettingRepository,
    protected ISettingRepository $settingRepository,
    protected IGroupRepository $groupRepository
  ) {
    parent::__construct('event_dates');
  }

  /**
   * @return Event[]
   */
  public function getAllEvents(): array {
    return Event::all()->all();
  }

  /**
   * @param int|null $year
   * @return Event[]
   */
  public function getDataOrderedByDate(int $year = null): array {
    $query = DB::table('event_dates');
    if ($year != null) {
      $query = $query->whereYear('date', '=', $year);
    }

    $events = [];

    $eventIdsResult = $query->orderBy('date')->get(['event_id', 'date'])->unique('event_id')->all();
    // DO NOT EVER use Event::find(Array) because it messes with the order
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
   * @param int[] $linkedBroadcasts
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
    array $linkedBroadcasts,
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

    //region Check linked broadcasts
    $linkedBroadcastsWhichHaveNotBeenDeleted = [];

    foreach ($event->getLinkedBroadcasts() as $oldLinkedBroadcast) {
      $toDelete = true;
      foreach ($linkedBroadcasts as $linkedBroadcastId) {
        if ($linkedBroadcastId == $oldLinkedBroadcast->broadcast_id) {
          $toDelete = false;
          $linkedBroadcastsWhichHaveNotBeenDeleted[] = $linkedBroadcastId;
          break;
        }
      }
      if ($toDelete) {
        DB::table('events_linked_broadcasts')->where('broadcast_id', '=', $oldLinkedBroadcast->broadcast_id)->delete();
      }
    }

    foreach ($linkedBroadcasts as $linkedBroadcast) {
      $toAdd = null;

      foreach ($linkedBroadcastsWhichHaveNotBeenDeleted as $linkedBroadcastIdNotDeleted) {
        if ($linkedBroadcast == $linkedBroadcastIdNotDeleted) {
          $toAdd = $linkedBroadcast;
          break;
        }
      }

      if ($toAdd != null) {
        $eventLinkedBroadcast = new EventLinkedBroadcast([
          'event_id' => $event->id,
          'broadcast_id' => $toAdd, ]);
        $eventLinkedBroadcast->save();
      }
    }
    //endregion

    //region Check decisions
    $decisionsWhichHaveNotBeenDeleted = [];

    foreach ($event->eventDecisions as $oldDecision) {
      $toDelete = true;

      foreach ($decisions as $decision) {
        if ($oldDecision->id == $decision['id']) {
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
      $decisionInDatabaseObject = null;

      foreach ($decisionsWhichHaveNotBeenDeleted as $decisionWhichHaveNotBeenDeleted) {
        if ($decision['id'] == $decisionWhichHaveNotBeenDeleted->id) {
          $decisionInDatabaseObject = $decisionWhichHaveNotBeenDeleted;
          break;
        }
      }

      if ($this->eventDecisionRepository->createOrUpdateEventDecision(
        $event,
        $decision['decision'],
        $decision['show_in_calendar'],
        $decision['color'],
        $decisionInDatabaseObject
      ) == null) {
        $this->deleteEvent($event);
        Logging::error('createOrUpdateEvent', 'Could not add or update event decision');

        return null;
      }
    }
    //endregion

    //region Check dates
    $datesWhichHaveNotBeenDeleted = [];
    foreach ($event->eventDates as $oldDate) {
      $toDelete = true;

      foreach ($dates as $date) {
        if ($oldDate->id == $date['id']) {
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
      $toAdd = true;

      foreach ($datesWhichHaveNotBeenDeleted as $dateWhichHasNotBeenDeleted) {
        if ($date['id'] == $dateWhichHasNotBeenDeleted->id) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        if ($this->eventDateRepository->createEventDate(
          $event,
          $date['x'],
          $date['y'],
          $date['date'],
          $date['location'],
          $date['description']
        ) == null) {
          $this->deleteEvent($event);
          Logging::error('createOrUpdateEvent', 'Could not add new event date');

          return null;
        }
      }
    }
    //endregion

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

    $date = DateHelper::getCurrentDateFormatted();
    $eventIdsResult = DB::table('event_dates')->where(
      'date',
      '>',
      $date
    )->orderBy('date')->get(['event_id', 'date'])->unique('event_id')->all();
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
      //region Event for everyone
      $groups = [];

      foreach ($this->groupRepository->getAllGroupsOrdered() as $group) {
        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $user) {
          $groupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser(
            $user,
            $event
          );
        }

        $subgroups = [];
        foreach ($group->getSubgroupsOrdered() as $subgroup) {
          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $subgroupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser($sUser, $event);
          }

          $subgroups[] = ['id' => $subgroup->id, 'name' => $subgroup->name,
            'users' => $anonymous ? [] : $subgroupResultUsers,
            'chart' => $calculateCharts ? $this->calculateChart(
              $eventDecisions,
              $subgroupResultUsers
            ) : [],
            'parent_group_name' => $subgroup->getGroup()->name,
            'parent_group_id' => $subgroup->group_id,];
        }

        $groups[] = ['id' => $group->id,
          'name' => $group->name,
          'users' => $anonymous ? [] : $groupResultUsers,
          'chart' => $calculateCharts ? $this->calculateChart($eventDecisions, $groupResultUsers) : [],
          'subgroups' => $subgroups,];
      }

      $results['groups'] = $groups;

      $allUsers = [];
      foreach ($this->userRepository->getAllUsersOrderedBySurname() as $user) {
        $allUsers[] = $this->eventDecisionRepository->getDecisionForUser($user, $event);
      }
      //endregion
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

          $subgroupToSave = ['id' => $subgroup->id,
            'name' => $subgroup->name,
            'parent_group_name' => $subgroup->getGroup()->name,
            'parent_group_id' => $subgroup->group_id,
            'users' => $anonymous ? [] : $subgroupResultUsers,
            'chart' => $calculateCharts ? $this->calculateChart(
              $eventDecisions,
              $subgroupResultUsers
            ) : [],];
          $subgroups[] = $subgroupToSave;
          $allSubgroupsIds[] = $subgroup->id;
        }

        $groups[] = ['id' => $group->id,
          'name' => $group->name,
          'users' => $anonymous ? [] : $groupResultUsers,
          'chart' => $calculateCharts ? $this->calculateChart($eventDecisions, $groupResultUsers) : [],
          'subgroups' => $subgroups,];
      }

      //region Single groups for events without parent group in event
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

          $subgroups[] = ['id' => $subgroup->id,
            'name' => $subgroup->name,
            'parent_group_name' => $subgroup->getGroup()->name,
            'parent_group_id' => $subgroup->group_id,
            'users' => $anonymous ? [] : $subgroupResultUsers,
            'chart' => $calculateCharts ? $this->calculateChart(
              $eventDecisions,
              $subgroupResultUsers
            ) : [],];
        }
      }
      // Add unknown group with single subgroups
      $groups[] = ['id' => -1, 'name' => 'unknown', 'users' => [], 'subgroups' => $subgroups];

      //endregion

      $results['groups'] = $groups;
    }

    $results['allUsers'] = $anonymous ? [] : $allUsers;
    $results['chart'] = $calculateCharts ? $this->calculateChart($eventDecisions, $allUsers) : [];

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
          'color' => $object->color,];
      }
    }

    return $chartElements;
  }
}
