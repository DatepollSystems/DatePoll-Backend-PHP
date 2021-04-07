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
  protected $fillable = ['role', 'subgroup_id', 'user_id', 'created_at', 'updated_at'];
  protected $with = ['subgroup', 'user'];

  /**
   * @return BelongsTo
   */
  public function subgroup(): BelongsTo {
    return $this->belongsTo(Subgroup::class);
  }

  /**
   * @return BelongsTo
   */
  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }
}
