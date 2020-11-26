<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $location
 * @property string $description
 * @property string $date
 * @property double $x
 * @property double $y
 * @property string $created_at
 * @property string $updated_at
 * @property Event $event
 */
class EventDate extends Model {
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'event_dates';

  /**
   * @var array
   */
  protected $fillable = [
    'event_id',
    'date',
    'description',
    'location',
    'x',
    'y',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Event
   */
  public function getEvent() {
    return $this->belongsTo('App\Models\Events\Event', 'event_id')
      ->first();
  }
}
