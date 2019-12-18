<?php

namespace App\Http\Controllers;

use App\Logging;
use App\Repositories\Setting\ISettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
  protected $settingRepository;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getCinemaFeatureIsEnabled() {
    return response()->json([
      'msg' => 'Is cinema service enabled',
      'enabled' => $this->settingRepository->getCinemaEnabled()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCinemaFeatureIsEnabled(Request $request) {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setCinemaEnabled($isEnabled);

    Logging::info("setCinemaFeatureIsEnabled", "User - " . $request->auth->id . " | Changed to " . $isEnabled);
    return response()->json([
      'msg' => 'Set cinema service enabled',
      'isEnabled' => $isEnabled]);
  }

  /**
   * @return JsonResponse
   */
  public function getEventsFeatureIsEnabled() {
    return response()->json([
      'msg' => 'Is events service enabled',
      'enabled' => $this->settingRepository->getEventsEnabled()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setEventsFeatureIsEnabled(Request $request) {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setEventsEnabled($isEnabled);

    Logging::info("setEventsFeatureIsEnabled", "User - " . $request->auth->id . " | Changed to " . $isEnabled);
    return response()->json([
      'msg' => 'Set events service enabled',
      'isEnabled' => $isEnabled]);
  }

  /**
   * @return JsonResponse
   */
  public function getCommunityName() {
    return response()->json([
      'msg' => 'Community name',
      'community_name' => $this->settingRepository->getCommunityName()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityName(Request $request) {
    $this->validate($request, ['community_name' => 'required|min:1|max:50']);

    $communityName = $request->input('community_name');

    $this->settingRepository->setCommunityName($communityName);

    Logging::info("setCommunityName", "User - " . $request->auth->id . " | Changed to " . $communityName);
    return response()->json([
      'msg' => 'Set community name',
      'community_name' => $communityName]);
  }

  /**
   * @return JsonResponse
   */
  public function getCommunityUrl() {
    return response()->json([
      'msg' => 'Community url',
      'community_url' => $this->settingRepository->getCommunityUrl()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityUrl(Request $request) {
    $this->validate($request, ['community_url' => 'required|min:1|max:128']);

    $communityUrl = $request->input('community_url');

    $this->settingRepository->setCommunityUrl($communityUrl);

    Logging::info("setCommunityUrl", "User - " . $request->auth->id . " | Changed to " . $communityUrl);
    return response()->json([
      'msg' => 'Set community url',
      'community_url' => $communityUrl]);
  }

  /**
   * @return JsonResponse
   */
  public function getUrl() {
    return response()->json([
      'msg' => 'Community url',
      'url' => $this->settingRepository->getUrl()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setUrl(Request $request) {
    $this->validate($request, ['url' => 'required|min:1|max:128']);

    $url = $request->input('url');

    $this->settingRepository->setUrl($url);

    Logging::info("setCommunityUrl", "User - " . $request->auth->id . " | Changed to " . $url);
    return response()->json([
      'msg' => 'Set url',
      'url' => $url]);
  }

  /**
   * @return JsonResponse
   */
  public function getOpenWeatherMapKey() {
    return response()->json([
      'msg' => 'OpenWeatherMap key',
      'openweathermap_key' => $this->settingRepository->getOpenWeatherMapKey()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setOpenWeatherMapKey(Request $request) {
    $this->validate($request, ['openweathermap_key' => 'required|max:50']);

    $openWeatherMapKey = $request->input('openweathermap_key');

    $this->settingRepository->setOpenWeatherMapKey($openWeatherMapKey);

    Logging::info("setOpenWeatherMapKey", "User - " . $request->auth->id . " | Changed to " . $openWeatherMapKey);
    return response()->json([
      'msg' => 'Set OpenWeatherMap key',
      'openweathermap_key' => $openWeatherMapKey]);
  }

  /**
   * @return JsonResponse
   */
  public function getOpenWeatherMapCinemaCityId() {
    return response()->json([
      'msg' => 'OpenWeatherMap cinema city id',
      'openweathermap_cinema_city_id' => $this->settingRepository->getCinemaOpenWeatherMapCityId()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setOpenWeatherMapCinemaCityId(Request $request) {
    $this->validate($request, ['openweathermap_cinema_city_id' => 'required|max:50']);

    $openWeatherMapCinemaCityId = $request->input('openweathermap_cinema_city_id');

    $this->settingRepository->setCinemaOpenWeatherMapCityId($openWeatherMapCinemaCityId);

    Logging::info("setOpenWeatherMapCinemaCityId", "User - " . $request->auth->id . " | Changed to " . $openWeatherMapCinemaCityId);
    return response()->json([
      'msg' => 'Set OpenWeatherMap cinema city id',
      'openweathermap_cinema_city_id' => $openWeatherMapCinemaCityId]);
  }
}
