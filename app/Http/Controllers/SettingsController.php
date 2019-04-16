<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
  /**
   * @return JsonResponse
   */
  public function getCinemaFeatureIsEnabled() {
    return response()->json(['msg' => 'Is cinema service enabled', 'enabled' => env('APP_CINEMA_ENABLED', false)], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCinemaFeatureIsEnabled(Request $request) {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->changeEnvironmentVariable('APP_CINEMA_ENABLED', $isEnabled);

    return response()->json(['msg' => 'Set cinema service enabled', 'isEnabled' => $isEnabled]);
  }

  /**
   * @param $key
   * @param $value
   */
  private function changeEnvironmentVariable($key, $value) {
    $path = base_path('.env');

    if (is_bool(env($key))) {
      $old = env($key) ? 'true' : 'false';
    } elseif (env($key) === null) {
      $old = 'null';
    } else {
      $old = env($key);
    }

    if (is_bool($value)) {
      if ($value) {
        $value = 'true';
      } else {
        $value = 'false';
      }
    }

    if (file_exists($path)) {
      file_put_contents($path, str_replace("$key=" . $old, "$key=" . $value, file_get_contents($path)));
    }
  }
}
