<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 */
class Setting extends Model {

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'settings';

  /**
   * @var array
   */
  protected $fillable = [
    'key',
    'value',
    'created_at',
    'updated_at', ];
}
