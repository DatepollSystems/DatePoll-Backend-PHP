<?php

namespace App\Models\User;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use stdClass;

/**
 * @property int $id
 * @property string $firstname
 * @property string $surname
 * @property string $join_date
 * @property string $internal_comment
 * @property string $created_at
 * @property string $updated_at
 */
class DeletedUser extends Model
{
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
    'updated_at'];
}
