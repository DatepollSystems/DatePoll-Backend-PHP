<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $decision
 * @property boolean $showInCalendar
 * @property string $color
 * @property string $created_at
 * @property string $updated_at
 */
class EventStandardDecision extends Model {
  protected $table = 'events_standard_decisions';

  protected $hidden = 'showInCalendar';

  /**
   * @var array
   */
  protected $fillable = ['decision', 'showInCalendar', 'color', 'created_at', 'updated_at'];

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['show_in_calendar'] = $this->showInCalendar;

    return $returnable;
  }
}
