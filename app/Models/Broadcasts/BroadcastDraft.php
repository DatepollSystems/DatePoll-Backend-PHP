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
 * @property string $created_at
 * @property string $updated_at
 */
class BroadcastDraft extends Model {

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'broadcast_drafts';

  /**
   * @var array
   */
  protected $fillable = [
    'subject',
    'bodyHTML',
    'body',
    'writer_user_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | User
   */
  public function writer() {
    return $this->belongsTo('App\Models\User\User', 'writer_user_id')->first();
  }
}
