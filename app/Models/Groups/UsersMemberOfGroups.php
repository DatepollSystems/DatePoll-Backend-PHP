<?php

namespace App\Models\Groups;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $role
 * @property int $group_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property Group $group
 * @property User $user
 */
class UsersMemberOfGroups extends Model {
  protected $fillable = ['role', 'group_id', 'user_id', 'created_at', 'updated_at'];
  protected $with = ['group', 'user'];

  /**
   * @return BelongsTo
   */
  public function group(): BelongsTo {
    return $this->belongsTo(Group::class);
  }

  /**
   * @return BelongsTo
   */
  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }
}
