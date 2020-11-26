<?php

namespace App\Repositories\User\UserChange;

use App\Logging;
use App\Models\User\UserChange;
use Illuminate\Database\Eloquent\Collection;

class UserChangeRepository implements IUserChangeRepository {
  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|null $newValue
   * @param string|null $oldValue
   * @return UserChange|null
   */
  public function createUserChange(string $property, int $userId, int $editorId, string $newValue = null, string $oldValue = null) {
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
   * @return UserChange[]|Collection
   */
  public function getAllUserChangesOrderedByDate() {
    return UserChange::orderBy('created_at', 'DESC')->get();
  }

  /**
   * @param int $id
   * @return UserChange|null
   */
  public function getUserChangeById(int $id) {
    return UserChange::find($id);
  }

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
  ) {
    if ($newValue != $oldValue) {
      $this->createUserChange($property, $userId, $editorId, $newValue, $oldValue);
    }
  }
}
