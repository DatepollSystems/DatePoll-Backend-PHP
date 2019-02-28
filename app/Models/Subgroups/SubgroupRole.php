<?php

namespace App\Models\Subgroups;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property UsersMemberOfSubgroups[] $usersMemberOfSubgroups
 */
class SubgroupRole extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')->get();
  }
}
