<?php

namespace App\Http\Middleware\Events;

use App\Repositories\Setting\ISettingRepository;
use Closure;
use Illuminate\Http\Request;

class EventsFeatureMiddleware
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
    if (!$this->settingRepository->getEventsEnabled()) {
      return response()->json(['msg' => 'The events feature is disabled on this DatePoll server'], 503);
    }

    return $next($request);
  }
}
