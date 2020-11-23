<?php

namespace App\Models\PerformanceBadge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property UserHavePerformanceBadgeWithInstrument[] $userHavePerformanceBadgeWithInstrument
 */
class Instrument extends Model {
  /**
   * @var array
   */
  protected $fillable = ['name', 'created_at', 'updated_at'];

  /**
   * @return HasMany
   */
  public function usersHaveBadgesWithInstruments() {
    return $this->hasMany('App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument');
  }
}
