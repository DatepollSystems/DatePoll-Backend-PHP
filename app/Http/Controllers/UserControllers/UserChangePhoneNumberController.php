<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Models\User\UserTelephoneNumber;
use App\Repositories\User\UserChange\IUserChangeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserChangePhoneNumberController extends Controller {
  protected IUserChangeRepository $userChangeRepository;

  public function __construct(IUserChangeRepository $userChangeRepository) {
    $this->userChangeRepository = $userChangeRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addPhoneNumber(AuthenticatedRequest $request): JsonResponse {
    $this->validate(
      $request,
      ['label' => 'required|string|min:1|max:190', 'number' => 'required|string|min:1|max:190']
    );

    $user = $request->auth;

    $phoneNumber = new UserTelephoneNumber(['label' => $request->input('label'), 'number' => $request->input('number'),
      'user_id' => $user->id, ]);

    if (! $phoneNumber->save()) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    $this->userChangeRepository->createUserChange(
      'phone number',
      $user->id,
      $user->id,
      $request->input('number'),
      null
    );

    return response()->json([
      'msg' => 'Added phone number',
      'phone_number' => $phoneNumber, ], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function removePhoneNumber(AuthenticatedRequest $request, int $id): JsonResponse {
    $phoneNumber = UserTelephoneNumber::find($id);
    if ($phoneNumber == null) {
      return response()->json(['msg' => 'Phone number not found', 'error_code' => 'phone_number_not_found'], 404);
    }

    if ($phoneNumber->user_id != $request->auth->id) {
      return response()->json([
        'msg' => 'Could not delete phone number because it does not belong to you!',
        'error_code' => 'phone_number_does_not_belong_to_you', ], 400);
    }

    $this->userChangeRepository->createUserChange(
      'phone number',
      $request->auth->id,
      $request->auth->id,
      null,
      $phoneNumber->number
    );
    if (! $phoneNumber->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = [
      'msg' => 'Phone number deleted',
      'phone_number_add' => [
        'href' => 'api/v1/user/myself/phoneNumber',
        'method' => 'POST',
        'params' => 'label, number', ], ];

    return response()->json($response);
  }
}
