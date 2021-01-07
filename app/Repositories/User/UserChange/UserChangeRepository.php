<?php

namespace App\Repositories\User\UserChange;

use App\Logging;
use App\Models\User\UserChange;
use App\Utils\Converter;
use App\Utils\TypeHelper;

class UserChangeRepository implements IUserChangeRepository {
  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|int|bool|null $newValue
   * @param string|int|bool|null $oldValue
   * @return UserChange|null
   */
  public function createUserChange(string $property, int $userId, int $editorId, string|int|bool|null $newValue = null, string|int|bool|null $oldValue = null): ?UserChange {
    if (TypeHelper::isBoolean($newValue)) {
      $newValue = Converter::booleanToString($newValue);
    } else if (TypeHelper::isInteger($newValue)) {
      $newValue = Converter::integerToString($newValue);
    }

    if (TypeHelper::isBoolean($oldValue)) {
      $newValue = Converter::booleanToString($oldValue);
    } else if (TypeHelper::isInteger($oldValue)) {
      $newValue = Converter::integerToString($oldValue);
    }

    $userChange = new UserChange([
      'property' => $property,
      'old_value' => $oldValue,
      'new_value' => $newValue,
      'editor_id' => $editorId,
      'user_id' => $userId, ]);

    if ($userChange->save()) {
      return $userChange;
    } else {
      Logging::error('createUserChange', 'Could not create user change!');

      return null;
    }
  }

  /**
   * @return UserChange[]
   */
  public function getAllUserChangesOrderedByDate(): array {
    return UserChange::orderBy('created_at', 'DESC')->get()->all();
  }

  /**
   * @param int $id
   * @return UserChange|null
   */
  public function getUserChangeById(int $id): ?UserChange {
    return UserChange::find($id);
  }

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
  ) {
    if ($newValue != $oldValue) {
      $this->createUserChange($property, $userId, $editorId, $newValue, $oldValue);
    }
  }
}
