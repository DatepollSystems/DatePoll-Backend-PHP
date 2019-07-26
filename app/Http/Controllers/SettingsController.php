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
    return response()->json(['msg' => 'Is cinema service enabled', 'enabled' => env('APP_FEATURE_CINEMA_ENABLED', false)], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCinemaFeatureIsEnabled(Request $request) {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->changeEnvironmentVariable('APP_FEATURE_CINEMA_ENABLED', $isEnabled);

    return response()->json(['msg' => 'Set cinema service enabled', 'isEnabled' => $isEnabled]);
  }

  /**
   * @return JsonResponse
   */
  public function getEventsFeatureIsEnabled() {
    return response()->json(['msg' => 'Is events service enabled', 'enabled' => env('APP_FEATURE_EVENTS_ENABLED', false)], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setEventsFeatureIsEnabled(Request $request) {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->changeEnvironmentVariable('APP_FEATURE_EVENTS_ENABLED', $isEnabled);

    return response()->json(['msg' => 'Set events service enabled', 'isEnabled' => $isEnabled]);
  }

  /**
   * @return JsonResponse
   */
  public function getCommunityName() {
    return response()->json(['msg' => 'Community name', 'community_name' => env('APP_COMMUNITY_NAME')], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityName(Request $request) {
    $this->validate($request, ['community_name' => 'required|min:1|max:50']);

    $communityName = $request->input('community_name');

    $this->changeEnvironmentVariable('APP_COMMUNITY_NAME', $communityName);

    return response()->json(['msg' => 'Set community name', 'community_name' => $communityName]);
  }

  /**
   * @return JsonResponse
   */
  public function getOpenWeatherMapKey() {
    return response()->json(['msg' => 'OpenWeatherMap key', 'openweathermap_key' => env('APP_OPENWEATHERMAP_KEY')], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setOpenWeatherMapKey(Request $request) {
    $this->validate($request, ['openweathermap_key' => 'required|max:50']);

    $openWeatherMapKey = $request->input('openweathermap_key');

    $this->changeEnvironmentVariable('APP_OPENWEATHERMAP_KEY', $openWeatherMapKey);

    return response()->json(['msg' => 'Set OpenWeatherMap key', 'openweathermap_key' => $openWeatherMapKey]);
  }

  /**
   * @return JsonResponse
   */
  public function getOpenWeatherMapCinemaCityId() {
    return response()->json(['msg' => 'OpenWeatherMap cinema city id', 'openweathermap_cinema_city_id' => env('APP_OPENWEATHERMAP_CINEMA_CITY_ID')], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setOpenWeatherMapCinemaCityId(Request $request) {
    $this->validate($request, ['openweathermap_cinema_city_id' => 'required|max:50']);

    $openWeatherMapCinemaCityId = $request->input('openweathermap_cinema_city_id');

    $this->changeEnvironmentVariable('APP_OPENWEATHERMAP_CINEMA_CITY_ID', $openWeatherMapCinemaCityId);

    return response()->json(['msg' => 'Set OpenWeatherMap cinema city id', 'openweathermap_cinema_city_id' => $openWeatherMapCinemaCityId]);
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
      $old = '"'.env($key).'"';
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
