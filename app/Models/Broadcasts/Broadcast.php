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

  // Hide it and manual add for_everyone in toArray()
  protected $hidden = ['forEveryone', 'bodyHTML'];

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
    'updated_at',];

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

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();

    $returnable['writer_name'] = $this->writer()->getCompleteName();
    $returnable['for_everyone'] = $this->forEveryone;

    $toReturnGroups = [];
    foreach ($this->broadcastsForGroups() as $group) {
      $group = $group->group();
      $toReturnGroup = new stdClass();
      $toReturnGroup->id = $group->id;
      $toReturnGroup->name = $group->name;
      $toReturnGroups[] = $toReturnGroup;
    }
    $returnable['groups'] = $toReturnGroups;

    $toReturnSubgroups = [];
    foreach ($this->broadcastsForSubgroups() as $subgroup) {
      $subgroup = $subgroup->subgroup();
      $toReturnSubgroup = new stdClass();
      $toReturnSubgroup->id = $subgroup->id;
      $toReturnSubgroup->name = $subgroup->name;
      $toReturnSubgroup->group_id = $subgroup->group_id;
      $toReturnSubgroup->group_name = $subgroup->group()->name;
      $toReturnSubgroups[] = $toReturnSubgroup;
    }
    $returnable['subgroups'] = $toReturnSubgroups;

    $toReturnAttachments = [];
    foreach ($this->attachments() as $attachment) {
      $toReturnAttachments[] = $attachment;
    }
    $returnable['attachments'] = $toReturnAttachments;

    return $returnable;
  }

  /**
   * @return array
   */
  public function toArrayWithBodyHTML(): array {
    $returnable = $this::toArray();
    $returnable['bodyHTML'] = $this->bodyHTML;
    return $returnable;
  }

  /**
   * @return array
   */
  public function toArrayWithBodyHTMLAndUserInfo(): array {
    $returnable = $this::toArrayWithBodyHTML();
    $returnable['users_info'] = BroadcastUserInfo::where('broadcast_id', '=', $this->id)
      ->orderBy('sent')
      ->get()->all();
    return $returnable;
  }
}
