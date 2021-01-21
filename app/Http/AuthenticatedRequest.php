<?php

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

  /** @noinspection PhpMissingParentConstructorInspection */
  public function __construct(Request $request) {
    foreach (get_object_vars($request) as $property => $value) {
      $this->$property = $value;
    }
  }
}
