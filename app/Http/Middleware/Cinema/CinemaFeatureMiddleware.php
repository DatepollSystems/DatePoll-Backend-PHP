<?php

namespace App\Http\Middleware\Cinema;

use App\Repositories\System\Setting\ISettingRepository;
use Closure;
use Illuminate\Http\Request;

class CinemaFeatureMiddleware {
  protected ISettingRepository $settingRepository;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    if (!$this->settingRepository->getCinemaEnabled()) {
      return response()->json([
                                'msg' => 'The cinema feature is disabled on this DatePoll server',
                                'error_code' => 'feature_disabled_cinema'], 503);
    }

    return $next($request);
  }
}
