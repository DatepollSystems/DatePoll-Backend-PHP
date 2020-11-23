<?php

namespace App\Http\Middleware\SeatReservation;

use App\Repositories\System\Setting\ISettingRepository;
use Closure;
use Illuminate\Http\Request;

class SeatReservationFeatureMiddleware {
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
    if (! $this->settingRepository->getSeatReservationEnabled()) {
      return response()->json([
        'msg' => 'The seat reservation feature is disabled on this DatePoll server',
        'error_code' => 'feature_disabled_seat_reservation', ], 503);
    }

    return $next($request);
  }
}
