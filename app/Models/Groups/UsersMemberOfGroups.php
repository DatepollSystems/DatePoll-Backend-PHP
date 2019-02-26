<?php

namespace App\Models\Groups;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $group_role_id
 * @property int $group_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property Group $group
 * @property GroupRole $groupRole
 * @property User $user
 */
class UsersMemberOfGroups extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['group_role_id', 'group_id', 'user_id', 'created_at', 'updated_at'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function group() {
    return $this->belongsTo('App\Group')->first();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function groupRole() {
    return $this->belongsTo('App\GroupRole')->first();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user() {
    return $this->belongsTo('App\User')->first();
  }
}
