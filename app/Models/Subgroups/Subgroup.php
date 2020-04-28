<?php

namespace App\Models\Subgroups;

use App\Models\Groups\Group;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $group_id
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property Group $group
 * @property UsersMemberOfSubgroups[] $usersMemberOfSubgroups
 */
class Subgroup extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['group_id', 'name', 'description', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo | Group
   */
  public function group() {
    return $this->belongsTo('App\Models\Groups\Group')->first();
  }

  /**
   * @return Collection | UsersMemberOfSubgroups[] | null
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')->get();
  }
}
