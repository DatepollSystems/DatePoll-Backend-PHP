<?php

namespace App\Repositories\User\UserChange;

use App\Logging;
use App\Models\User\UserChange;
use App\Utils\Converter;
use App\Utils\StringHelper;
use App\Utils\TypeHelper;
use Illuminate\Support\Facades\DB;

class UserChangeRepository implements IUserChangeRepository {
  /**
   * @param string $property
   * @param int $userId
   * @param int $editorId
   * @param string|int|bool|null $newValue
   * @param string|int|bool|null $oldValue
   * @return UserChange|null
   */
  public function createUserChange(string $property, int $userId, int $editorId, string|int|bool|null $newValue = null,
                                   string|int|bool|null $oldValue = null): ?UserChange {
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
      'user_id' => $userId,]);

    if ($userChange->save()) {
      return $userChange;
    }

    Logging::error('createUserChange', 'Could not create user change!');

    return null;
  }

  /**
   * @param int $page
   * @param int $pageSize
   * @return UserChange[]
   */
  public function getAllUserChangesOrderedByDate(int $page = 0, int $pageSize = 15): array {
    return UserChange::orderBy('created_at', 'DESC')->skip($page * $pageSize)->take($pageSize)->get()->all();
  }

  /**
   * @param int $id
   * @return UserChange|null
   */
  public function getUserChangeById(int $id): ?UserChange {
    return UserChange::find($id);
  }

  /**
   * @param string $search
   * @param bool $ignoreEditor
   * @return array
   */
  public function searchUserChange(string $search, $ignoreEditor = false): array {
    $search = '%' . $search . '%';

    $query = DB::table('users_changes')
      ->join('users as users', 'users.id', '=', 'users_changes.user_id')
      ->join('users as editors', 'editors.id', '=', 'users_changes.editor_id')
      ->where(DB::raw('concat(users.firstname, " ", users.surname)'), 'like', $search)
      ->orWhere('property', 'like', $search)
      ->orWhere('old_value', 'like', $search)
      ->orWhere('new_value', 'like', $search);

    if (! $ignoreEditor) {
      $query = $query->orWhere(DB::raw('concat(editors.firstname, " ", editors.surname)'), 'like', $search);
    }

    return $query->select('users_changes.id as id', 'users_changes.user_id as user_id',
      'users_changes.editor_id as editor_id',
      'users.firstname as user_firstname',
      'users.surname as user_surname',
      'editors.firstname as editor_firstname',
      'editors.surname as editor_surname',
      'users_changes.property as property',
      'users_changes.old_value as old_value',
      'users_changes.new_value as new_value',
      'users_changes.created_at as created_at',
      'users_changes.updated_at as updated_at')
      ->get()->all();
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
  ): void {
    if ($newValue != $oldValue) {
      $this->createUserChange($property, $userId, $editorId, $newValue, $oldValue);
    }
  }
}
