<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\System\Setting\ISettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
  protected ISettingRepository $settingRepository;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
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
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setBroadcastFeatureIsEnabled(Request $request) {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setBroadcastsEnabled($isEnabled);

    Logging::info("setBroadcastFeatureIsEnabled", "User - " . $request->auth->id . " | Changed to " . $isEnabled);
    return response()->json([
      'msg' => 'Set broadcast service enabled',
      'isEnabled' => $isEnabled]);
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
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityDescription(Request $request) {
    $this->validate($request, ['community_description' => 'required|min:1']);

    $communityDescription = $request->input('community_description');

    $this->settingRepository->setCommunityDescription($communityDescription);

    Logging::info("setCommunityDescription", "User - " . $request->auth->id . " | Changed");
    return response()->json([
      'msg' => 'Set community description',
      'community_description' => $communityDescription]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityImprint(Request $request) {
    $this->validate($request, ['community_imprint' => 'required|min:1']);

    $communityImprint = $request->input('community_imprint');

    $this->settingRepository->setCommunityImprint($communityImprint);

    Logging::info("setCommunityImprint", "User - " . $request->auth->id . " | Changed");
    return response()->json([
      'msg' => 'Set community imprint',
      'community_url' => $communityImprint]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityPrivacyPolicy(Request $request) {
    $this->validate($request, ['community_privacy_policy' => 'required|min:1']);

    $communityPrivacyPolicy = $request->input('community_privacy_policy');

    $this->settingRepository->setCommunityPrivacyPolicy($communityPrivacyPolicy);

    Logging::info("setCommunityPrivacyPolicy", "User - " . $request->auth->id . " | Changed");
    return response()->json([
      'msg' => 'Set community privacy policy',
      'community_url' => $communityPrivacyPolicy]);
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

  /**
   * @return JsonResponse
   */
  public function getHappyAlert() {
    return response()->json([
      'msg' => 'Happy alert',
      'happy_alert' => $this->settingRepository->getHappyAlert()], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setHappyAlert(Request $request) {
    $this->validate($request, ['happy_alert' => 'string']);

    $happyAlert = $request->input('happy_alert');

    $this->settingRepository->setHappyAlert($happyAlert);

    Logging::info("setHappyAlert", "User - " . $request->auth->id . " | Changed to " . $happyAlert);
    return response()->json([
      'msg' => 'Set happy alert key',
      'happy_alert' => $happyAlert]);
  }
}
