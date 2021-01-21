<?php

namespace App\Models\PerformanceBadge;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $description
 * @property int $afterYears
 * @property string $created_at
 * @property string $updated_at
 */
class Badge extends Model {
  protected $table = 'badges';

  /**
   * @var array
   */
  protected $fillable = [
    'description',
    'afterYears',
    'created_at',
    'updated_at', ];
}
