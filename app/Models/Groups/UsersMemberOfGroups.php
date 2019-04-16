<?php

namespace App\Models\Groups;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $group_role_id
 * @property int $group_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property Group $group
 * @property User $user
 */
class UsersMemberOfGroups extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['role', 'group_id', 'user_id', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo
   */
  public function group() {
    return $this->belongsTo('App\Models\Groups\Group')->first();
  }

  /**
   * @return BelongsTo
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')->first();
  }
}
