<?php

namespace App\Models\Groups;

use App\Models\Subgroups\Subgroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property Subgroup[] $subgroups
 * @property UsersMemberOfGroups[] $usersMemberOfGroups
 */
class Group extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

  /**
   * @return Collection | Subgroup | null
   */
  public function subgroups() {
    return $this->hasMany('App\Models\Subgroups\Subgroup')->get();
  }

  /**
   * @return Collection | UsersMemberOfGroups[] | null
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')->get();
  }
}
