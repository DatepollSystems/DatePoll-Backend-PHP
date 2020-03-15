<?php


namespace App\Repositories\Event\EventDecision;


use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use Exception;
use stdClass;

class EventDecisionRepository implements IEventDecisionRepository
{
  /**
   * @param int $id
   * @return EventDecision
   */
  public function getEventDecisionById(int $id) {
    return EventDecision::find($id);
  }

  /**
   * @param EventDecision $decision
   * @return bool|null
   * @throws Exception
   */
  public function deleteEventDecision(EventDecision $decision) {
    return $decision->delete();
  }

  /**
   * @param Event $event
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color
   * @param EventDecision|null $eventDecision
   * @return EventDecision
   */
  public function createOrUpdateEventDecision(Event $event, string $decision, bool $showInCalendar, string $color, EventDecision $eventDecision = null) {
    if ($eventDecision == null) {
      $eventDecision = new EventDecision([
        'event_id' => $event->id,
        'decision' => $decision,
        'showInCalendar' => $showInCalendar,
        'color' => $color]);
    } else {
      $eventDecision->decision = $decision;
      $eventDecision->showInCalendar = $showInCalendar;
      $eventDecision->color = $color;
    }

    return $eventDecision->save() ? $eventDecision : null;
  }

  /**
   * @param User $user
   * @param Event $event
   * @param bool $anonymous
   * @return stdClass
   */
  public function getDecisionForUser(User $user, Event $event, $anonymous = true) {
    $userToSave = new stdClass();
    if (!$anonymous) {
      $userToSave->id = $user->id;
      $userToSave->firstname = $user->firstname;
      $userToSave->surname = $user->surname;
    } else {
      $userToSave->id = null;
      $userToSave->firstname = null;
      $userToSave->surname = null;
    }

    $decision = EventUserVotedForDecision::where('user_id', $user->id)
                                         ->where('event_id', $event->id)
                                         ->first();
    $userToSave->additional_information = null;
    if ($decision == null) {
      $userToSave->decisionId = null;
      $userToSave->decision = null;
    } else {
      $userToSave->decisionId = $decision->decision()->id;
      $userToSave->decision = $decision->decision()->decision;
      if (!$anonymous) {
        $userToSave->additional_information = $decision->additionalInformation;
      }
    }

    return $userToSave;
  }

}