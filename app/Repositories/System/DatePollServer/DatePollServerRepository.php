<?php

namespace App\Repositories\System\DatePollServer;

use App\Repositories\System\Setting\ISettingRepository;
use App\Versions;
use Illuminate\Support\Facades\DB;
use stdClass;

class DatePollServerRepository implements IDatePollServerRepository
{

  protected $settingRepository = null;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * @return stdClass
   */
  public function getServerInfo() {
    $dto = new stdClass();
    $dto->version = Versions::getApplicationVersionString();
    $dto->version_number = Versions::getApplicationVersion();
    $dto->application_url = $this->settingRepository->getUrl();

    $dto->community_name = $this->settingRepository->getCommunityName();
    $dto->community_url = $this->settingRepository->getCommunityUrl();
    $dto->community_description = $this->settingRepository->getCommunityDescription();
    $dto->community_imprint = $this->settingRepository->getCommunityImprint();
    $dto->community_privacy_policy = $this->settingRepository->getCommunityPrivacyPolicy();

    $dto->logged_interactions_count = DB::table('logs')->count();

    $dto->events_enabled = $this->settingRepository->getEventsEnabled();
    $dto->events_count = DB::table('events')->count();
    $dto->event_votes_count = DB::table('events_users_voted_for')->count();
    $dto->event_decisions_count = DB::table('events_decisions')->count();
    $dto->event_dates_count = DB::table('event_dates')->count();

    $dto->cinema_enabled = $this->settingRepository->getCinemaEnabled();
    $dto->movies_count = DB::table('movies')->count();
    $dto->movies_tickets_count = DB::table('movies_bookings')->count();
    $movies_workers_count = DB::table('movies')->where('worker_id', '!=', null)->count();
    $dto->movies_workers_count = $movies_workers_count + DB::table('movies')->where('emergency_worker_id', '!=', null)->count();

    $dto->users_count = DB::table('users')->count();
    $dto->user_email_addresses_count = DB::table('user_email_addresses')->count();
    $dto->user_phone_numbers_count = DB::table('user_telephone_numbers')->count();

    $dto->performance_badges_count = DB::table('users_have_badges_with_instruments')->count();

    return $dto;
  }
}