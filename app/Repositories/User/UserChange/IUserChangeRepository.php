<?php

namespace App\Repositories\User\UserChange;

use App\Models\User\UserChange;

interface IUserChangeRepository {
  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|null $newValue
   * @param string|null $oldValue
   * @return UserChange|null
   */
  public function createUserChange(string $property, int $userId, int $editorId, string $newValue = null, string $oldValue = null): ?UserChange;

  /**
   * @return UserChange[]
   */
  public function getAllUserChangesOrderedByDate(): array;

  /**
   * @param int $id
   * @return UserChange|null
   */
  public function getUserChangeById(int $id): ?UserChange;

  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|null $newValue
   * @param string|null $oldValue
   */
  public function checkForPropertyChange(
    string $property,
    int $userId,
    int $editorId,
    ?string $newValue,
    ?string $oldValue
  );
}
