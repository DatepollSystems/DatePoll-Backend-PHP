<?php

namespace App\Models\Subgroups;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $role
 * @property int $subgroup_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property Subgroup $subgroup
 * @property User $user
 */
class UsersMemberOfSubgroups extends Model {
  /**
   * @var array
   */
  protected $fillable = ['role', 'subgroup_id', 'user_id', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo | Subgroup
   */
  public function subgroup(): BelongsTo|Subgroup {
    return $this->belongsTo(Subgroup::class)->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }
}
