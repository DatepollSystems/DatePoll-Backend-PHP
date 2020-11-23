<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

abstract class SettingValueType {
  const STRING = 'string';
  const BOOLEAN = 'boolean';
  const INTEGER = 'integer';
}

/**
 * @property int $id
 * @property string $type
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
    'type',
    'key',
    'value',
    'created_at',
    'updated_at', ];
}
