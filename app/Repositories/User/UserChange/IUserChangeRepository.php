<?php

namespace App\Repositories\User\UserChange;

use App\Models\User\UserChange;

interface IUserChangeRepository {
  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|int|bool|null $newValue
   * @param string|int|bool|null $oldValue
   * @return UserChange|null
   */
  public function createUserChange(string $property, int $userId, int $editorId, string|int|bool|null $newValue = null, string|int|bool|null $oldValue = null): ?UserChange;

  /**
   * @param int $page
   * @param int $pageSize
   * @return UserChange[]
   */
  public function getAllUserChangesOrderedByDate(int $page = 0, int $pageSize = 15): array;

  /**
   * @param int $id
   * @return UserChange|null
   */
  public function getUserChangeById(int $id): ?UserChange;

  /**
   * @param string $search
   * @param bool $ignoreEditor
   * @return array
   */
  public function searchUserChange(string $search, $ignoreEditor = false): array;

  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|int|bool|null $newValue
   * @param string|int|bool|null $oldValue
   */
  public function checkForPropertyChange(
    string $property,
    int $userId,
    int $editorId,
    string|int|bool|null $newValue,
    string|int|bool|null $oldValue
  ): void;
}
