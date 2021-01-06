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

  /**
   * @var array
   */
  protected $fillable = ['decision', 'showInCalendar', 'color', 'created_at', 'updated_at'];
}
