<?php

namespace App\Http\Middleware\Broadcasts;

use App\Repositories\System\Setting\ISettingRepository;
use Closure;
use Illuminate\Http\Request;

class BroadcastsFeatureMiddleware
{

  protected $settingRepository = null;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    if (!$this->settingRepository->getBroadcastsEnabled()) {
      return response()->json(['msg' => 'The broadcast feature is disabled on this DatePoll server'], 503);
    }

    return $next($request);
  }
}
