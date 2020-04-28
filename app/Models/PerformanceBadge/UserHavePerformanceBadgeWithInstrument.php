<?php

namespace App\Models\PerformanceBadge;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $performance_badge_id
 * @property int $instrument_id
 * @property string $created_at
 * @property string $updated_at
 * @property Instrument $instrument
 * @property PerformanceBadge $performanceBadge
 * @property User $user
 */
class UserHavePerformanceBadgeWithInstrument extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'users_have_badges_with_instruments';

  /**
   * @var array
   */
  protected $fillable = ['grade', 'date', 'note', 'user_id', 'performance_badge_id', 'instrument_id', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo | Instrument
   */
  public function instrument() {
    return $this->belongsTo('App\Models\PerformanceBadge\Instrument')->first();
  }

  /**
   * @return BelongsTo | PerformanceBadge
   */
  public function performanceBadge() {
    return $this->belongsTo('App\Models\PerformanceBadge\PerformanceBadge')->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')->first();
  }
}
