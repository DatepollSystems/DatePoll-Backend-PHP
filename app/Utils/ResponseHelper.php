<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;

abstract class ResponseHelper {

  /**
   * @param string ...$requiredPermissions
   * @return JsonResponse
   */
  public static function getPermissionDeniedError(string ...$requiredPermissions): JsonResponse {
    return response()->json([
      'msg' => 'Permission denied',
      'error_code' => 'permissions_denied',
      'needed_permissions' => [$requiredPermissions],], 403);
  }
}
