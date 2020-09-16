<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Repositories\System\DatePollServer\IDatePollServerRepository;
use Illuminate\Http\JsonResponse;

class DatePollServerController extends Controller
{

  protected IDatePollServerRepository $datePollServerRepository;

  public function __construct(IDatePollServerRepository $datePollServerRepository) {
    $this->datePollServerRepository = $datePollServerRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getServerInfo() {
    return response()->json($this->datePollServerRepository->getServerInfo(), 200);
  }
}
