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
}
