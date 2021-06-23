<?php

namespace App\Repositories\User\DeletedUser;

use App\Logging;
use App\Models\User\DeletedUser;
use App\Models\User\User;
use Exception;
use Illuminate\Support\Facades\DB;

class DeletedUserRepository implements IDeletedUserRepository {
  /**
   * @return DeletedUser[]
   */
  public function getDeletedUsers(): array {
    return DeletedUser::all()->all();
  }

  /**
   * @param User $user
   * @return bool
   * @throws Exception
   */
  public function deleteUser(User $user): bool {
    $deletedUser = new DeletedUser([
      'firstname' => $user->firstname,
      'surname' => $user->surname,
      'join_date' => $user->join_date,
      'internal_comment' => $user->internal_comment, ]);

    if ($deletedUser->save()) {
      try {
        return $user->delete();
      } catch (Exception $e) {
        Logging::error('deleteUser', 'Exception: ' . $e->getMessage());

        return false;
      }
    } else {
      Logging::error('deleteUser', 'Could not create deleted user - : ' . $user->id);

      return false;
    }
  }

  /**
   * @param int $id
   */
  public function deleteSingleDeletedUser(int $id): void {
    DB::table('users_deleted')->delete($id);
  }

  public function deleteAllDeletedUsers(): void {
    DB::table('users_deleted')
      ->delete();
  }
}
