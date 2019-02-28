<?php

namespace App\Models\Groups;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property UsersMemberOfGroup[] $usersMemberOfGroups
 */
class GroupRole extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')->get();
  }
}
