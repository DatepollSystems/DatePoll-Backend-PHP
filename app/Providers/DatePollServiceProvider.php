<?php

namespace App\Providers;

use App\Repositories\Broadcast\Broadcast\BroadcastRepository;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Broadcast\BroadcastAttachment\BroadcastAttachmentRepository;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Repositories\Broadcast\BroadcastDraft\BroadcastDraftRepository;
use App\Repositories\Broadcast\BroadcastDraft\IBroadcastDraftRepository;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\Movie\MovieRepository;
use App\Repositories\Cinema\MovieBooking\IMovieBookingRepository;
use App\Repositories\Cinema\MovieBooking\MovieBookingRepository;
use App\Repositories\Cinema\MovieWorker\IMovieWorkerRepository;
use App\Repositories\Cinema\MovieWorker\MovieWorkerRepository;
use App\Repositories\Cinema\MovieYear\IMovieYearRepository;
use App\Repositories\Cinema\MovieYear\MovieYearRepository;
use App\Repositories\Event\Event\EventRepository;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\EventDateRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\Event\EventDecision\EventDecisionRepository;
use App\Repositories\Event\EventDecision\IEventDecisionRepository;
use App\Repositories\Event\EventStandardDecision\EventStandardDecisionRepository;
use App\Repositories\Event\EventStandardDecision\IEventStandardDecisionRepository;
use App\Repositories\Event\EventStandardLocation\IEventStandardLocationRepository;
use App\Repositories\Event\EventStandardLocation\EventStandardLocationRepository;
use App\Repositories\Group\Group\GroupRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\Group\Subgroup\ISubgroupRepository;
use App\Repositories\Group\Subgroup\SubgroupRepository;
use App\Repositories\SeatReservation\Place\IPlaceRepository;
use App\Repositories\SeatReservation\Place\PlaceRepository;
use App\Repositories\SeatReservation\UserSeatReservation\IUserSeatReservationRepository;
use App\Repositories\SeatReservation\UserSeatReservation\UserSeatReservationRepository;
use App\Repositories\System\DatePollServer\DatePollServerRepository;
use App\Repositories\System\DatePollServer\IDatePollServerRepository;
use App\Repositories\System\Job\IJobRepository;
use App\Repositories\System\Job\JobRepository;
use App\Repositories\System\Log\ILogRepository;
use App\Repositories\System\Log\LogRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\System\Setting\SettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\User\UserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use App\Repositories\User\UserChange\UserChangeRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Repositories\User\UserSetting\UserSettingRepository;
use App\Repositories\User\UserToken\IUserTokenRepository;
use App\Repositories\User\UserToken\UserTokenRepository;
use Illuminate\Support\ServiceProvider;

class DatePollServiceProvider extends ServiceProvider
{
  public function register() {
    /** Cinema repositories */
    $this->app->bind(IMovieRepository::class, MovieRepository::class);
    $this->app->bind(IMovieWorkerRepository::class, MovieWorkerRepository::class);
    $this->app->bind(IMovieYearRepository::class, MovieYearRepository::class);
    $this->app->bind(IMovieBookingRepository::class, MovieBookingRepository::class);

    /** User repositories */
    $this->app->bind(IUserRepository::class, UserRepository::class);
    $this->app->bind(IUserTokenRepository::class, UserTokenRepository::class);
    $this->app->bind(IUserSettingRepository::class, UserSettingRepository::class);
    $this->app->bind(IUserChangeRepository::class, UserChangeRepository::class);

    /** Group repositories */
    $this->app->bind(IGroupRepository::class, GroupRepository::class);
    $this->app->bind(ISubgroupRepository::class, SubgroupRepository::class);

    /** Event repositories */
    $this->app->bind(IEventRepository::class, EventRepository::class);
    $this->app->bind(IEventDateRepository::class, EventDateRepository::class);
    $this->app->bind(IEventDecisionRepository::class, EventDecisionRepository::class);
    $this->app->bind(IEventStandardLocationRepository::class, EventStandardLocationRepository::class);
    $this->app->bind(IEventStandardDecisionRepository::class, EventStandardDecisionRepository::class);

    /** Broadcast repositories */
    $this->app->bind(IBroadcastRepository::class, BroadcastRepository::class);
    $this->app->bind(IBroadcastDraftRepository::class, BroadcastDraftRepository::class);
    $this->app->bind(IBroadcastAttachmentRepository::class, BroadcastAttachmentRepository::class);

    /** Seat repositories */
    $this->app->bind(IPlaceRepository::class, PlaceRepository::class);
    $this->app->bind(IUserSeatReservationRepository::class, UserSeatReservationRepository::class);

    /** System repositories */
    $this->app->bind(ISettingRepository::class, SettingRepository::class);
    $this->app->bind(IJobRepository::class, JobRepository::class);
    $this->app->bind(ILogRepository::class, LogRepository::class);
    $this->app->bind(IDatePollServerRepository::class, DatePollServerRepository::class);
  }
}
