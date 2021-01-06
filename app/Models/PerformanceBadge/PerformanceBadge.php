<?php

namespace App\Models\PerformanceBadge;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property UserHavePerformanceBadgeWithInstrument[] $userHavePerformanceBadgeWithInstrument
 */
class PerformanceBadge extends Model {
  /**
   * @var array
   */
  protected $fillable = ['name', 'created_at', 'updated_at'];

  /**
   * @return UserHavePerformanceBadgeWithInstrument[]
   */
  public function usersHaveBadgesWithInstruments(): array {
    return $this->hasMany(UserHavePerformanceBadgeWithInstrument::class)->get()->all();
  }
}
