<?php

namespace App\Models\Subgroups;

use Illuminate\Database\Eloquent\Model;

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
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function group() {
    return $this->belongsTo('App\Group')->first();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\UsersMemberOfSubgroups')->get();
  }
}
