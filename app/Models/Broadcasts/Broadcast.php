<?php

namespace App\Models\Broadcasts;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
class Broadcast extends Model {
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
    'updated_at', ];

  /**
   * @return BelongsTo | User
   */
  public function writer(): BelongsTo|User {
    return $this->belongsTo(User::class, 'writer_user_id')->first();
  }

  /**
   * @return BroadcastForGroup[]
   */
  public function broadcastsForGroups(): array {
    return $this->hasMany(BroadcastForGroup::class)
      ->get()->all();
  }

  /**
   * @return BroadcastForSubgroup[]
   */
  public function broadcastsForSubgroups(): array {
    return $this->hasMany(BroadcastForSubgroup::class)
      ->get()->all();
  }

  /**
   * @return BroadcastUserInfo[]
   */
  public function usersInfo(): array {
    return $this->hasMany(BroadcastUserInfo::class)
      ->get()->all();
  }

  /**
   * @return BroadcastAttachment[]
   */
  public function attachments(): array {
    return $this->hasMany(BroadcastAttachment::class)
      ->get()->all();
  }
}
