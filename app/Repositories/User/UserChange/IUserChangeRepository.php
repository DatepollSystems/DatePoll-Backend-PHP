<?php

namespace App\Repositories\User\UserChange;

use App\Models\User\UserChange;
use Illuminate\Database\Eloquent\Collection;

interface IUserChangeRepository
{
  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|null $newValue
   * @param string|null $oldValue
   * @return UserChange|null
   */
  public function createUserChange(string $property, int $userId, int $editorId, string $newValue = null,
                                   string $oldValue = null);

  /**
   * @return UserChange[]|Collection
   */
  public function getAllUserChangesOrderedByDate();

  /**
   * @param int $id
   * @return UserChange|null
   */
  public function getUserChangeById(int $id);
}
