<?php

namespace App\Repositories\User\UserChange;

use App\Models\User\UserChange;

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
  public function createUserChange(string $property, int $userId, int $editorId, string $newValue = null, string $oldValue = null);
}
