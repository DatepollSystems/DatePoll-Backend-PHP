<?php

namespace App\Models\Broadcasts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $path
 * @property string $name
 * @property string $token
 * @property int $broadcast_id
 * @property Broadcast $broadcast
 * @property string $created_at
 * @property string $updated_at
 */
class BroadcastAttachment extends Model {

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'broadcast_attachments';

  protected $hidden = ['path'];

  /**
   * @var array
   */
  protected $fillable = [
    'path',
    'name',
    'token',
    'broadcast_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Broadcast
   */
  public function broadcast() {
    return $this->belongsTo(Broadcast::class, 'broadcast_id')->first();
  }
}
