<?php

namespace App\Models\Groups;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class GroupPermission extends Model {
  /**
   * @var array
   */
  protected $fillable = ['group_id', 'permission', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo|Group
   */
  public function group(): BelongsTo|Group {
    return $this->belongsTo(Group::class)->first();
  }
}
