<?php

namespace App\Repositories\User\DeletedUser;

use App\Models\User\DeletedUser;
use App\Models\User\User;

interface IDeletedUserRepository {
  /**
   * @return DeletedUser[]
   */
  public function getDeletedUsers(): array;

  /**
   * @param User $user
   * @return bool
   */
  public function deleteUser(User $user): bool;

  public function deleteAllDeletedUsers();
}
