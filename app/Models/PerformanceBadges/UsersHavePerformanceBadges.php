<?php

namespace App\Models\PerformanceBadges;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $performance_badge_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property PerformanceBadge $performanceBadge
 * @property User $user
 */
class UsersHavePerformanceBadges extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['performance_badge_id', 'user_id', 'created_at', 'updated_at'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function performanceBadge() {
    return $this->belongsTo('App\Models\PerformanceBadges\PerformanceBadge')->first();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user() {
    return $this->belongsTo('App\Models\User')->first();
  }
}
