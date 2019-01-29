<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
//  /**
//   * The request instance.
//   *
//   * @var \Illuminate\Http\Request
//   */
//  private $request;
//
//  /**
//   * Create a new controller instance.
//   *
//   * @param  \Illuminate\Http\Request $request
//   * @return void
//   */
//  public function __construct(Request $request)
//  {
//    $this->request = $request;
//  }

  /**
   * Create a new token.
   *
   * @param $userID
   * @return string
   */
  protected function jwt($userID)
  {
    $payload = [
      'iss' => "lumen-jwt", // Issuer of the token
      'sub' => $userID, // Subject of the token
      'iat' => time(), // Time when JWT was issued.
      'exp' => time() + 60 * 60 // Expiration time
    ];

    // As you can see we are passing `JWT_SECRET` as the second parameter that will
    // be used to decode the token in the future.
    return JWT::encode($payload, env('JWT_SECRET'));
  }

  /**
   * Authenticate a user and return the token if the provided credentials are correct.
   *
   * @param Request $request
   * @return mixed
   * @throws \Illuminate\Validation\ValidationException
   */
  public function signin(Request $request)
  {
    $this->validate($request, [
      'email' => 'required|email',
      'password' => 'required'
    ]);
    // Find the user by email
    $user = User::where('email', $request->input('email'))->first();
    if (!$user) {
      // You wil probably have some sort of helpers or whatever
      // to make sure that you have the same response format for
      // differents kind of responses. But let's return the
      // below respose for now.
      return response()->json([
        'error' => 'Email or password is wrong'
      ], 400);
    }
    // Verify the password and generate the token
    if (Hash::check($request->input('password'), $user->password)) {
      return response()->json([
        'token' => $this->jwt($user->id)
      ], 200);
    }
    // Bad Request response
    return response()->json([
      'error' => 'Email or password is wrong'
    ], 400);
  }

  public function refresh(Request $request) {
    $this->validate($request, [
      'token' => 'required'
    ]);

    $payload = JWT::decode($request->input('token'), env('JWT_SECRET'), ['HS256']);

    $payload_array = (array) $payload;
    $userID = $payload_array['sub'];

    return response()->json([
      'token' => $this->jwt($userID)
    ], 200);
  }
}
