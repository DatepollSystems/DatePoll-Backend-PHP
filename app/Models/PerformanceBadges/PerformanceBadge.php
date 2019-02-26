<?php

namespace App\Models\PerformanceBadges;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property UsersHavePerformanceBadges[] $usersHavePerformanceBadges
 */
class PerformanceBadge extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function usersHavePerformanceBadges() {
    return $this->hasMany('App\Models\PerformanceBadges\UsersHavePerformanceBadge')->get();
  }
}
