<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @property int $id
 * @property int $user_id
 * @property string $label
 * @property string $number
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class UserTelephoneNumber extends Model {
  /**
   * @var string[]
   */
  protected $fillable = ['user_id', 'label', 'number', 'created_at', 'updated_at'];

  /**
   * @var string[]
   */
  protected $hidden = ['user_id', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo|User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }

  #[ArrayShape(['id' => "int", 'user_id' => "int", 'label' => "string", 'number' => "string", 'created_at' => "string",
                'updated_at' => "string"])]
  public function toArray(): array {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'label' => $this->label,
      'number' => $this->number,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
