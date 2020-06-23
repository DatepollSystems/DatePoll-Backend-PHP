<?php

namespace App\Models\PerformanceBadge;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * @property int $id
 * @property int $user_id
 * @property string $description
 * @property Date $getDate
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserHasBadge extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'users_has_badges';

  /**
   * @var array
   */
  protected $fillable = [
    'description',
    'getDate',
    'reason',
    'user_id',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | User
   */
  public function user() {
    return $this->belongsTo('App\Models\User\User')
                ->first();
  }
}
