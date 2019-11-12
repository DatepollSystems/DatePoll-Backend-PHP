<?php

namespace App\Models\System;

use App\LogTypes;
use Illuminate\Database\Eloquent\Model;
use stdClass;
/**
 * @property int $id
 * @property LogTypes $type
 * @property string $message
 * @property string $created_at
 * @property string $updated_at
 */
class Log extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'logs';

  /**
   * @var array
   */
  protected $fillable = [
    'type',
    'message',
    'created_at',
    'updated_at'];

  /**
   * @return stdClass
   */
  public function getReturnable() {
    $returnable = new stdClass();

    $returnable->id = $this->id;
    $returnable->type = $this->type;
    $returnable->message = $this->message;
    $returnable->created_at = $this->created_at;
    $returnable->updated_at = $this->updated_at;

    $returnable->delete_log = ['href' => 'api/v1/system/logs/' . $this->id, 'method' => 'DELETE'];

    return $returnable;
  }
}
