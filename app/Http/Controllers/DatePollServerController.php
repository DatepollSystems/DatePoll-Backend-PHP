<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User\UserTelephoneNumber;
use App\Repositories\System\DatePollServer\IDatePollServerRepository;
use App\Versions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class DatePollServerController extends Controller
{

  protected $datePollServerRepository = null;

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
