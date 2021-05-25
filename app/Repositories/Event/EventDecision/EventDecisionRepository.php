<?php

namespace App\Repositories\Event\EventDecision;

use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

class EventDecisionRepository implements IEventDecisionRepository {
  /**
   * @param EventDecision $decision
   * @return bool
   * @throws Exception
   */
  public function deleteEventDecision(EventDecision $decision): bool {
    return $decision->delete();
  }

  /**
   * @param Event $event
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color
   * @param EventDecision|null $eventDecision
   * @return EventDecision|null
   */
  public function createOrUpdateEventDecision(Event $event, string $decision, bool $showInCalendar, string $color, EventDecision $eventDecision = null): ?EventDecision {
    if ($eventDecision == null) {
      $eventDecision = new EventDecision([
        'event_id' => $event->id,
        'decision' => $decision,
        'showInCalendar' => $showInCalendar,
        'color' => $color,]);
    } else {
      $eventDecision->decision = $decision;
      $eventDecision->showInCalendar = $showInCalendar;
      $eventDecision->color = $color;
    }

    return $eventDecision->save() ? $eventDecision : null;
  }

  /**
   * @param int $id
   * @return EventDecision[]
   */
  public function getEventDecisionsByEventId(int $id): array {
    return EventDecision::where('event_id', '=', $id)->get()->all();
  }

  /**
   * @param User $user
   * @param Event $event
   * @return array
   */
  #[ArrayShape(['id' => "int|null", 'firstname' => "null|string", 'surname' => "null|string",
                'decisionId' => "mixed", 'decision' => "mixed",
                'additional_information' => "mixed"])]
  public function getDecisionForUser(User $user, Event $event): array {
    $additionalInformation = null;
    $id = $user->id;
    $firstname = $user->firstname;
    $surname = $user->surname;

    $decision = EventUserVotedForDecision::where('user_id', $user->id)
      ->where('event_id', $event->id)
      ->first();
    if ($decision != null) {
      $additionalInformation = $decision->additionalInformation;
    }

    return ['id' => $id, 'firstname' => $firstname, 'surname' => $surname, 'decisionId' => $decision?->decision_id,
            'decision' => $decision?->decision()->decision, 'additional_information' => $additionalInformation];
  }
}
