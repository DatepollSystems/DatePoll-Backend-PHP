<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace App\Http;

use App\Models\User\User;
use Illuminate\Http\Request;

/**
 * Class Request
 * @package App\Http
 * @property User $auth
 */
class AuthenticatedRequest extends Request {
  public User $auth;

  public function __construct(Request $request) {
    foreach ($request as $property => $value) {
      $this->$property = $value;
    }
  }
}
