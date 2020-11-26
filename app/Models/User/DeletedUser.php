<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $firstname
 * @property string $surname
 * @property string $join_date
 * @property string $internal_comment
 * @property string $created_at
 * @property string $updated_at
 */
class DeletedUser extends Model {
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'users_deleted';

  /**
   * @var array
   */
  protected $fillable = [
    'firstname',
    'surname',
    'join_date',
    'internal_comment',
    'created_at',
    'updated_at', ];
}
