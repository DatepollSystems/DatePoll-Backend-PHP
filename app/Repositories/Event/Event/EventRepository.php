<?php

namespace App\Repositories\Event\Event;

use App\Jobs\CreateNewEventEmailsJob;
use App\Logging;
use App\Models\Events\Event;
use App\Models\User\User;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\ArrayHelper;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

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

    return Event::find(ArrayHelper::getPropertyArrayOfObjectArray(
      $query->orderBy('date')->addSelect('event_id')->get()->toArray(),
      'event_id'
    ))->all();
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
    foreach ($event->getEventDates() as $oldDate) {
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
  public function deleteEvent(Event $event): bool {
    if (! $event->delete()) {
      Logging::error('deleteEvent', 'Could not delete event');

      return false;
    } else {
      return true;
    }
  }

  /**
   * @param Event $event
   * @param bool $anonymous
   * @return array
   */
  public function getResultsForEvent(Event $event, bool $anonymous): array {
    $results = [];
    $results['anonymous'] = $anonymous;

    if ($event->forEveryone) {
      $groups = [];

      foreach ($this->groupRepository->getAllGroupsOrdered() as $group) {
        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $userMemberOfGroup) {
          $groupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser(
            $userMemberOfGroup,
            $event,
            $anonymous
          );
        }

        $subgroups = [];
        foreach ($group->getSubgroupsOrdered() as $subgroup) {
          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $subgroupResultUsers[] = $this->eventDecisionRepository->getDecisionForUser($sUser, $event, $anonymous);
          }

          $subgroups[] = ['id' => $subgroup->id, 'name' => $subgroup->name, 'users' => $subgroupResultUsers,
                          'parent_group_name' => $subgroup->group()->name, 'parent_group_id' => $subgroup->group_id];
        }

        $groups[] = ['id' => $group->id, 'name' => $group->name, 'users' => $groupResultUsers,
                     'subgroups' => $subgroups];
      }

      $results['groups'] = $groups;

      $allUsers = [];
      // Directly use User:: methods because in the UserRepository we already use the EventRepository and that would be
      // a circular dependency and RAM will explodes
      foreach ($this->userRepository->getAllUsersOrderedBySurname() as $user) {
        $allUsers[] = $this->eventDecisionRepository->getDecisionForUser($user, $event, $anonymous);
      }

      $results['allUsers'] = $allUsers;
    } else {
      $allUsers = [];
      $allUserIds = [];
      $allSubgroupsIds = [];

      $groups = [];

      foreach ($event->getGroupsOrdered() as $group) {
        $groupResultUsers = [];
        foreach ($group->getUsersOrderedBySurname() as $gUser) {
          $user = $this->eventDecisionRepository->getDecisionForUser($gUser, $event, $anonymous);
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
            $user = $this->eventDecisionRepository->getDecisionForUser($sUser, $event, $anonymous);
            $subgroupResultUsers[] = $user;
            if (! ArrayHelper::inArray($allUserIds, $sUser->id)) {
              $allUsers[] = $user;
              $allUserIds[] = $sUser->id;
            }
          }

          $subgroupToSave = ['id' => $subgroup->id, 'name' => $subgroup->name,
                             'parent_group_name' => $subgroup->group()->name, 'parent_group_id' => $subgroup->group_id,
                             'users' => $subgroupResultUsers];
          $subgroups[] = $subgroupToSave;
          $allSubgroupsIds[] = $subgroup->id;
        }
        $groups[] = ['id' => $group->id, 'name' => $group->name, 'users' => $groupResultUsers,
                     'subgroups' => $subgroups];
      }

      $subgroups = [];
      foreach ($event->getSubgroupsOrdered() as $subgroup) {
        if (! ArrayHelper::inArray($allSubgroupsIds, $subgroup->id)) {
          $subgroupResultUsers = [];
          foreach ($subgroup->getUsersOrderedBySurname() as $sUser) {
            $user = $this->eventDecisionRepository->getDecisionForUser($sUser, $event, $anonymous);
            $subgroupResultUsers[] = $user;
            if (! in_array($sUser->id, $allUserIds)) {
              $allUsers[] = $user;
              $allUserIds[] = $sUser->id;
            }
          }

          $subgroups[] = ['id' => $subgroup->id, 'name' => $subgroup->name,
                          'parent_group_name' => $subgroup->group()->name, 'parent_group_id' => $subgroup->group_id,
                          'users' => $subgroupResultUsers];
        }
      }
      // Add unknown group with single subgroups
      $groups[] = ['id' => -1, 'name' => 'unknown', 'users' => [], 'subgroups' => $subgroups];

      $results['groups'] = $groups;

      $results['allUsers'] = $allUsers;
    }

    return $results;
  }

  /**
   * @param User $user
   * @return array
   */
  public function getOpenEventsForUser(User $user): array {
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
        $events[] = $event->toArrayWithUserDecisionByUserId($user->id);
      }
    }

    return $events;
  }

  /**
   * @param Event $event
   * @return array
   */
  public function getPotentialVotersForEvent(Event $event): array {
    return $this->getResultsForEvent($event, false)['allUsers'];
  }
}
