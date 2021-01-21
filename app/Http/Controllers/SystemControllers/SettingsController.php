<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\System\Setting\ISettingRepository;
use App\Utils\ArrayHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller {
  protected ISettingRepository $settingRepository;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCinemaFeatureIsEnabled(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setCinemaEnabled($isEnabled);

    Logging::info('setCinemaFeatureIsEnabled', 'User - ' . $request->auth->id . ' | Changed to ' . $isEnabled);

    return response()->json([
      'msg' => 'Set cinema service enabled',
      'isEnabled' => $isEnabled, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setEventsFeatureIsEnabled(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setEventsEnabled($isEnabled);

    Logging::info('setEventsFeatureIsEnabled', 'User - ' . $request->auth->id . ' | Changed to ' . $isEnabled);

    return response()->json([
      'msg' => 'Set events service enabled',
      'isEnabled' => $isEnabled, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setBroadcastFeatureIsEnabled(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setBroadcastsEnabled($isEnabled);

    Logging::info('setBroadcastFeatureIsEnabled', 'User - ' . $request->auth->id . ' | Changed to ' . $isEnabled);

    return response()->json([
      'msg' => 'Set broadcast service enabled',
      'isEnabled' => $isEnabled, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setBroadcastProcessIncomingEmailsFeatureIsEnabled(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setBroadcastsProcessIncomingEmailsEnabled($isEnabled);

    Logging::info('setBroadcastsProcessIncomingEmailsEnabled', 'User - ' . $request->auth->id . ' | Changed to ' . $isEnabled);

    return response()->json([
      'msg' => 'Set broadcast process incoming emails service enabled',
      'isEnabled' => $isEnabled, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setBroadcastsProcessIncomingEmailsForwardingIsEnabled(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['isEnabled' => 'required|boolean']);

    $isEnabled = $request->input('isEnabled');

    $this->settingRepository->setBroadcastsProcessIncomingEmailsForwardingEnabled($isEnabled);

    Logging::info('setBroadcastsProcessIncomingEmailsForwardingEnabled', 'User - ' . $request->auth->id . ' | Changed to ' . $isEnabled);

    return response()->json([
      'msg' => 'Set broadcast process incoming emails forwarding service enabled',
      'isEnabled' => $isEnabled, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setBroadcastsProcessIncomingEmailsForwardingEmailAddresses(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['email_addresses' => 'array', 'email_addresses.*' => 'required|max:190',]);

    $emailAddresses = $request->input('email_addresses');
    if (! ArrayHelper::isArray($emailAddresses)) {
      $emailAddresses = [];
    }

    $emailAddresses = $this->settingRepository->setBroadcastsProcessIncomingEmailsForwardingEmailAddresses($emailAddresses);

    Logging::info('setBroadcastsProcessIncomingEmailsForwardingEnabled', 'User - ' . $request->auth->id . ' | Changed');

    return response()->json([
      'msg' => 'Set broadcast process incoming emails forwarding email addresses set ',
      'email_addresses' => $emailAddresses, ]);
  }

  /**
   * @return JsonResponse
   */
  public function getBroadcastsProcessIncomingEmailsForwardingEmailAddresses(): JsonResponse {
    return response()->json(
      [
        'msg' => 'Broadcast process incoming mails forwarding email addresses',
        'email_addresses' => $this->settingRepository->getBroadcastsProcessIncomingEmailsForwardingEmailAddresses(), ],
      200
    );
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityName(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['community_name' => 'required|min:1|max:50']);

    $communityName = $request->input('community_name');

    $this->settingRepository->setCommunityName($communityName);

    Logging::info('setCommunityName', 'User - ' . $request->auth->id . ' | Changed to ' . $communityName);

    return response()->json([
      'msg' => 'Set community name',
      'community_name' => $communityName, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityUrl(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['community_url' => 'required|min:1|max:128']);

    $communityUrl = $request->input('community_url');

    $this->settingRepository->setCommunityUrl($communityUrl);

    Logging::info('setCommunityUrl', 'User - ' . $request->auth->id . ' | Changed to ' . $communityUrl);

    return response()->json([
      'msg' => 'Set community url',
      'community_url' => $communityUrl, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityDescription(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['community_description' => 'required|min:1']);

    $communityDescription = $request->input('community_description');

    $this->settingRepository->setCommunityDescription($communityDescription);

    Logging::info('setCommunityDescription', 'User - ' . $request->auth->id . ' | Changed');

    return response()->json([
      'msg' => 'Set community description',
      'community_description' => $communityDescription, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityImprint(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['community_imprint' => 'required|min:1']);

    $communityImprint = $request->input('community_imprint');

    $this->settingRepository->setCommunityImprint($communityImprint);

    Logging::info('setCommunityImprint', 'User - ' . $request->auth->id . ' | Changed');

    return response()->json([
      'msg' => 'Set community imprint',
      'community_url' => $communityImprint, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setCommunityPrivacyPolicy(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['community_privacy_policy' => 'required|min:1']);

    $communityPrivacyPolicy = $request->input('community_privacy_policy');

    $this->settingRepository->setCommunityPrivacyPolicy($communityPrivacyPolicy);

    Logging::info('setCommunityPrivacyPolicy', 'User - ' . $request->auth->id . ' | Changed');

    return response()->json([
      'msg' => 'Set community privacy policy',
      'community_url' => $communityPrivacyPolicy, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setUrl(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['url' => 'required|min:1|max:128']);

    $url = $request->input('url');

    $this->settingRepository->setUrl($url);

    Logging::info('setCommunityUrl', 'User - ' . $request->auth->id . ' | Changed to ' . $url);

    return response()->json([
      'msg' => 'Set url',
      'url' => $url, ]);
  }

  /**
   * @return JsonResponse
   */
  public function getOpenWeatherMapKey(): JsonResponse {
    return response()->json([
      'msg' => 'OpenWeatherMap key',
      'openweathermap_key' => $this->settingRepository->getOpenWeatherMapKey(), ], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setOpenWeatherMapKey(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['openweathermap_key' => 'required|max:50']);

    $openWeatherMapKey = $request->input('openweathermap_key');

    $this->settingRepository->setOpenWeatherMapKey($openWeatherMapKey);

    Logging::info('setOpenWeatherMapKey', 'User - ' . $request->auth->id . ' | Changed to ' . $openWeatherMapKey);

    return response()->json([
      'msg' => 'Set OpenWeatherMap key',
      'openweathermap_key' => $openWeatherMapKey, ]);
  }

  /**
   * @return JsonResponse
   */
  public function getOpenWeatherMapCinemaCityId(): JsonResponse {
    return response()->json(
      [
        'msg' => 'OpenWeatherMap cinema city id',
        'openweathermap_cinema_city_id' => $this->settingRepository->getCinemaOpenWeatherMapCityId(), ],
      200
    );
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setOpenWeatherMapCinemaCityId(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['openweathermap_cinema_city_id' => 'required|max:50']);

    $openWeatherMapCinemaCityId = $request->input('openweathermap_cinema_city_id');

    $this->settingRepository->setCinemaOpenWeatherMapCityId($openWeatherMapCinemaCityId);

    Logging::info(
      'setOpenWeatherMapCinemaCityId',
      'User - ' . $request->auth->id . ' | Changed to ' . $openWeatherMapCinemaCityId
    );

    return response()->json([
      'msg' => 'Set OpenWeatherMap cinema city id',
      'openweathermap_cinema_city_id' => $openWeatherMapCinemaCityId, ]);
  }

  /**
   * @return JsonResponse
   */
  public function getAlert(): JsonResponse {
    return response()->json([
      'msg' => 'Alert',
      'alert' => $this->settingRepository->getAlert(), ], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setAlert(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['message' => 'string|min:0|max:190', 'type' => 'required|string|min:1|max:190']);

    $alertMessage = $request->input('message');
    $aType = $request->input('type');

    if (! str_contains($aType, 'happy') && ! str_contains($aType, 'normal')) {
      return response()->json(['msg' => 'Unknown type', 'error_code' => 'unknown_alert_type',
        'possible types' => ['happy', 'normal'], ], 422);
    }

    $alert = $this->settingRepository->setAlert($alertMessage, $aType);

    Logging::info('setAlert', 'User - ' . $request->auth->id . ' | Changed to ' . $alert->message);

    return response()->json([
      'msg' => 'Set alert',
      'alert' => $alert, ]);
  }
}
