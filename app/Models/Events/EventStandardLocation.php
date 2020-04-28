<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $location
 * @property double $x
 * @property double $y
 * @property string $created_at
 * @property string $updated_at
 */
class EventStandardLocation extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'events_standard_locations';

  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'location',
    'x',
    'y',
    'created_at',
    'updated_at'];

}
