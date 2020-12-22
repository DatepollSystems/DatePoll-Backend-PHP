<?php

namespace App\Models\PerformanceBadge;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'users_have_badges_with_instruments';

  /**
   * @var array
   */
  protected $fillable = ['grade', 'date', 'note', 'user_id', 'performance_badge_id', 'instrument_id', 'created_at', 'updated_at'];

  /**
   * @return Instrument | BelongsTo | null
   */
  public function instrument() {
    return $this->belongsTo(Instrument::class)->first();
  }

  /**
   * @return PerformanceBadge | null | BelongsTo
   */
  public function performanceBadge(): ?PerformanceBadge {
    return $this->belongsTo(PerformanceBadge::class)->first();
  }

  /**
   * @return User | null | BelongsTo
   */
  public function user(): ?User {
    return $this->belongsTo(User::class)->first();
  }

  /**
   * @return array
   */
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
