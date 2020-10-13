<?php

namespace App\Models\Broadcasts;

use App\Models\Groups\Group;
use App\Models\User\User;
use App\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use stdClass;

/**
 * @property int $id
 * @property string $subject
 * @property string $bodyHTML
 * @property string $body
 * @property User $writer
 * @property int $writer_user_id
 * @property boolean $forEveryone
 * @property string $created_at
 * @property string $updated_at
 * @property BroadcastForGroup[] $broadcastsForGroups
 * @property BroadcastForSubgroup[] $broadcastsForSubgroups
 * @property BroadcastUserInfo[] $usersInfo
 * @property BroadcastAttachment[] $broadcastAttachment
 */
class Broadcast extends Model
{

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'broadcasts';

  /**
   * @var array
   */
  protected $fillable = [
    'subject',
    'bodyHTML',
    'body',
    'writer_user_id',
    'forEveryone',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | User
   */
  public function writer() {
    return $this->belongsTo('App\Models\User\User', 'writer_user_id')->first();
  }

  /**
   * @return Collection | BroadcastForGroup[] | null
   */
  public function broadcastsForGroups() {
    return $this->hasMany('App\Models\Broadcasts\BroadcastForGroup')
                ->get();
  }

  /**
   * @return Collection | BroadcastForSubgroup[] | null
   */
  public function broadcastsForSubgroups() {
    return $this->hasMany('App\Models\Broadcasts\BroadcastForSubgroup')
                ->get();
  }

  /**
   * @return Collection | BroadcastUserInfo[] | null
   */
  public function usersInfo() {
    return $this->hasMany('App\Models\Broadcasts\BroadcastUserInfo')
                ->get();
  }

  /**
   * @return Collection | BroadcastAttachment[] | null
   */
  public function attachments() {
    return $this->hasMany(BroadcastAttachment::class)
      ->get();
  }
}
