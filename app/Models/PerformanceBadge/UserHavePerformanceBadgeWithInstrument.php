<?php

namespace App\Models\PerformanceBadge;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @property int $id
 * @property int $user_id
 * @property int $performance_badge_id
 * @property int $instrument_id
 * @property string $grade
 * @property string $date
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
 * @property Instrument $instrument
 * @property PerformanceBadge $performanceBadge
 * @property User $user
 */
class UserHavePerformanceBadgeWithInstrument extends Model {
  protected $table = 'users_have_badges_with_instruments';

  /**
   * @var array
   */
  protected $fillable = ['grade', 'date', 'note', 'user_id', 'performance_badge_id', 'instrument_id', 'created_at', 'updated_at'];

  /**
   * @return Instrument | BelongsTo
   */
  public function instrument(): BelongsTo|Instrument {
    return $this->belongsTo(Instrument::class)->first();
  }

  /**
   * @return PerformanceBadge | BelongsTo
   */
  public function performanceBadge(): PerformanceBadge|BelongsTo {
    return $this->belongsTo(PerformanceBadge::class)->first();
  }

  /**
   * @return User | BelongsTo
   */
  public function user(): User|BelongsTo {
    return $this->belongsTo(User::class)->first();
  }

  /**
   * @return array
   */
  #[ArrayShape(['id' => "int", 'performanceBadge_id' => "int", 'instrument_id' => "int", 'grade' => "string", 'note' => "string", 'performanceBadge_name' => "string", 'instrument_name' => "string", 'date' => "null|string"])]
  public function toArray(): array {
    $date = null;
    if ($this->date != '1970-01-01') {
      $date = $this->date;
    }

    return [
      'id' => $this->id,
      'performanceBadge_id' => $this->performance_badge_id,
      'instrument_id' => $this->instrument_id,
      'grade' => $this->grade,
      'note' => $this->note,
      'performanceBadge_name' => $this->performanceBadge()->name,
      'instrument_name' => $this->instrument()->name,
      'date' => $date,
    ];
  }
}
