<?php

namespace App\Http\Controllers;

use App\Models\Events\Event;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\MovieBooking\IMovieBookingRepository;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Repositories\User\UserToken\IUserTokenRepository;
use App\Utils\Converter;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Exception\CalendarEventException;
use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\Model\Description\Geo;
use Jsvrcek\ICS\Model\Description\Location;
use Jsvrcek\ICS\Model\Relationship\Organizer;
use Jsvrcek\ICS\Utility\Formatter;

class CalendarController extends Controller {
  private static string $completeCalendarCacheKey = 'calendar.complete';
  private static string $personalCalendarCacheKey = 'calendar.personal.';

  public function __construct(
    protected IUserTokenRepository $userTokenRepository,
    protected ISettingRepository $settingRepository,
    protected IUserRepository $userRepository,
    protected IUserSettingRepository $userSettingRepository,
    protected IMovieRepository $movieRepository,
    protected IEventRepository $eventRepository,
    protected IMovieBookingRepository $movieBookingRepository
  ) {
  }

  /**
   * @param string $token
   * @return JsonResponse|null
   * @throws CalendarEventException
   * @throws Exception
   */
  public function getCalendarOf(string $token): ?JsonResponse {
    $tokenObject = $this->userTokenRepository->getUserTokenByTokenAndPurpose($token, 'calendar');
    if ($tokenObject == null) {
      return response()->json(['msg' => 'Provided token is incorrect', 'error_code' => 'token_incorrect'], 401);
    }

    $cacheKey = CalendarController::$personalCalendarCacheKey . $tokenObject->user_id;
    if (Cache::has($cacheKey)) {
      header('Content-type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename="calendar.ics"');
      echo Cache::get($cacheKey);

      return null;
    }

    $user = $tokenObject->user();

    $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
    $calendar = new Calendar();
    $timezone = 'Europe/Vienna';
    $calendar->setTimezone(new DateTimeZone($timezone));
    $calendar->setProdId('-//DatePoll//PersonalCalendar//DE');

    $calendar->setCustomHeaders([
      'X-WR-TIMEZONE' => $timezone,
      // Support e.g. Google Calendar -> https://blog.jonudell.net/2011/10/17/x-wr-timezone-considered-harmful/
      'X-WR-CALNAME' => 'Personal DatePoll calendar',
      // https://en.wikipedia.org/wiki/ICalendar
      'X-PUBLISHED-TTL' => 'PT15M',
      // update calendar every 15 minutes
    ]);

    $appOrganizer = new Organizer(new Formatter());
    $appOrganizer->setValue(env('MAIL_FROM_ADDRESS'))
      ->setName($this->settingRepository->getCommunityName())
      ->setLanguage('de');

    if ($this->settingRepository->getCinemaEnabled() && $this->userSettingRepository->getShowMoviesInCalendarForUser($user)) {
      /* -------- Movie booking specific calendar -------------*/
      $movies = $this->movieBookingRepository->getMoviesWhereUserBookedTickets($user->id);

      foreach ($movies as $movie) {
        $geo = new Geo();
        $geo->setLatitude(48.643865);
        $geo->setLongitude(15.814679);

        $location = new Location();
        $location->setLanguage('de');
        $location->setName('Kanzlerturm Wiese Eggenburg');

        $movieEvent = new CalendarEvent();
        $movieEvent->setStart(new DateTime($movie->date . 'T20:30:00'))
          ->setEnd(new DateTime($movie->date . 'T23:59:59'))
          ->setSummary($movie->name)
          ->setDescription('Reservierte Karten: ' . $movie->getBookedTicketsForUser($user->id))
          ->setUrl($movie->trailerLink)
          ->setGeo($geo)
          ->setSequence(1)
          ->setStatus('CONFIRMED')
          ->setCreated(new DateTime($movie->created_at))
          ->addLocation($location)
          ->setUid('movie' . $movie->id);

        if ($movie->worker_name != null) {
          $organizer = new Organizer(new Formatter());
          $organizer->setValue(Converter::integerToString($movie->worker_id))
            ->setName($movie->worker()->getCompleteName())
            ->setLanguage('de');
          $movieEvent->setOrganizer($organizer);
        } else {
          $movieEvent->setOrganizer($appOrganizer);
        }

        $calendar->addEvent($movieEvent);
      }

      /* -------- Movie worker specific calendar -------------*/
      foreach ($user->workerMovies() as $movie) {
        $movieAlreadyInCalendar = false;
        foreach ($movies as $movieB) {
          if ($movieB->id === $movie->id) {
            $movieAlreadyInCalendar = true;
            break;
          }
        }

        if (! $movieAlreadyInCalendar) {
          $geo = new Geo();
          $geo->setLatitude(48.643865);
          $geo->setLongitude(15.814679);

          $location = new Location();
          $location->setLanguage('de');
          $location->setName('Kanzlerturm Wiese Eggenburg');

          $movieEvent = new CalendarEvent();
          $movieEvent->setStart(new DateTime($movie->date . 'T20:30:00'))
            ->setEnd(new DateTime($movie->date . 'T23:59:59'))
            ->setSummary($movie->name)
            ->setUrl($movie->trailerLink)
            ->setSequence(1)
            ->setCreated(new DateTime($movie->created_at))
            ->setGeo($geo)
            ->setStatus('CONFIRMED')
            ->addLocation($location)
            ->setUid('movie' . $movie->id);

          if ($movie->worker_name != null) {
            $organizer = new Organizer(new Formatter());
            $organizer->setValue(Converter::integerToString($movie->worker_id))
              ->setName($movie->worker()->getCompleteName())
              ->setLanguage('de');
            $movieEvent->setOrganizer($organizer);
          } else {
            $movieEvent->setOrganizer($appOrganizer);
          }

          $calendar->addEvent($movieEvent);
        }
      }
    }

    if ($this->settingRepository->getEventsEnabled() && $this->userSettingRepository->getShowEventsInCalendarForUser($user)) {
      // Find events where user answered a question with decision which also has showInCalendar on true
      $eventIds = DB::table('events_users_voted_for')
        ->join('events_decisions', 'events_decisions.id', '=', 'events_users_voted_for.decision_id')
        ->join('events', 'events.id', '=', 'events_users_voted_for.event_id')
        ->where('events_decisions.showInCalendar', '=', 1)
        ->where('events_users_voted_for.user_id', '=', $user->id)
        ->addSelect('events.id')
        ->get();

      $events = [];
      foreach ($eventIds as $eventId) {
        $events[] = Event::find($eventId->id);
      }

      foreach ($events as $event) {
        $startDate = $event->getFirstEventDate();

        $eventEvent = new CalendarEvent();
        $eventEvent->setStart(new DateTime($startDate->date))
          ->setEnd(new DateTime($event->getLastEventDate()->date))
          ->setSummary($event->name)
          ->setSequence(1)
          ->setStatus('CONFIRMED')
          ->setCreated(new DateTime($event->created_at))
          ->setOrganizer($appOrganizer)
          ->setUid('event' . $event->id);

        if ($startDate->x != -199 && $startDate->y != -199) {
          $geo = new Geo();
          $geo->setLatitude($startDate->x);
          $geo->setLongitude($startDate->y);

          $eventEvent->setGeo($geo);
        }

        if ($event->description != null) {
          if (strlen($event->description) > 2) {
            $eventEvent->setDescription($event->description);
          }
        }

        if ($startDate->location != null) {
          if (strlen($startDate->location) > 0) {
            $location = new Location();
            $location->setLanguage('de');
            $location->setName($startDate->location);
            $eventEvent->addLocation($location);
          }
        }

        $calendar->addEvent($eventEvent);
      }
    }

    if ($this->userSettingRepository->getShowBirthdaysInCalendarForUser($user)) {
      $users = $this->userRepository->getAllUsers();
      foreach ($users as $user) {
        if ($this->userSettingRepository->getShareBirthdayForUser($user)) {
          $d = date_parse_from_format('Y-m-d', $user->birthday);
          if ($d['month'] == date('n')) {
            $birthdayEvent = new CalendarEvent();
            $birthdayEvent->setStart(new DateTime($this->updateDate($user->birthday) . 'T00:00:01'))
              ->setEnd(new DateTime($this->updateDate($user->birthday) . 'T00:00:02'))
              ->setAllDay(true)
              ->setSequence(1)
              ->setStatus('CONFIRMED')
              ->setCreated(new DateTime($user->created_at))
              ->setOrganizer($appOrganizer)
              ->setSummary($user->getCompleteName() . '\'s Geburtstag')
              ->setUid('userBirthday' . $user->id);

            $calendar->addEvent($birthdayEvent);
          }
        }
      }
    }
    $calendarExport->addCalendar($calendar);
    $stream = $calendarExport->getStream();

    Cache::put($cacheKey, $stream, 60 * 60);

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="'. $token . '.ics"');
    echo $calendarExport->getStream();

    return null;
  }

  /**
   * @param string $dateString
   * @return string
   * @throws Exception
   */
  private function updateDate(string $dateString): string {
    $suppliedDate = new DateTime($dateString);
    $currentYear = (int)(new DateTime())->format('Y');

    return $currentYear . '-' . (int)$suppliedDate->format('m') . '-' . (int)$suppliedDate->format('d');
  }

  /**
   * @throws CalendarEventException
   * @throws Exception
   */
  public function getCompleteCalendar() {
    if (Cache::has(CalendarController::$completeCalendarCacheKey)) {
      header('Content-type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename="calendar.ics"');
      echo Cache::get(CalendarController::$completeCalendarCacheKey);

      return null;
    }

    $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
    $calendar = new Calendar();
    $timezone = 'Europe/Vienna';
    $calendar->setTimezone(new DateTimeZone($timezone));
    $calendar->setProdId('-//DatePoll//CompleteCalendar//DE');

    $calendar->setCustomHeaders([
      'X-WR-TIMEZONE' => $timezone,
      // Support e.g. Google Calendar -> https://blog.jonudell.net/2011/10/17/x-wr-timezone-considered-harmful/
      'X-WR-CALNAME' => 'Complete DatePoll calendar',
      // https://en.wikipedia.org/wiki/ICalendar
      'X-PUBLISHED-TTL' => 'PT15M',
      // update calendar every 15 minutes
    ]);

    $appOrganizer = new Organizer(new Formatter());
    $appOrganizer->setValue(env('MAIL_FROM_ADDRESS'))
      ->setName($this->settingRepository->getCommunityName())
      ->setLanguage('de');

    if ($this->settingRepository->getCinemaEnabled()) {
      foreach ($this->movieRepository->getAllMoviesOrderedByDate() as $movie) {
        $geo = new Geo();
        $geo->setLatitude(48.643865);
        $geo->setLongitude(15.814679);

        $location = new Location();
        $location->setLanguage('de');
        $location->setName('Kanzlerturm Wiese Eggenburg');

        $movieEvent = new CalendarEvent();
        $movieEvent->setStart(new DateTime($movie->date . 'T20:30:00'))
          ->setEnd(new DateTime($movie->date . 'T23:59:59'))
          ->setSummary($movie->name)
          ->setDescription('Insgesamt reservierte Karten: ' . $movie->bookedTickets)
          ->setUrl($movie->trailerLink)
          ->setGeo($geo)
          ->setSequence(1)
          ->setStatus('CONFIRMED')
          ->setCreated(new DateTime($movie->created_at))
          ->addLocation($location)
          ->setOrganizer($appOrganizer)
          ->setUid('allMovie' . $movie->id);

        $calendar->addEvent($movieEvent);
      }
    }

    if ($this->settingRepository->getEventsEnabled()) {
      foreach ($this->eventRepository->getAllEvents() as $event) {
        $startDate = $event->getFirstEventDate();

        $geo = new Geo();
        $geo->setLatitude($startDate->x);
        $geo->setLongitude($startDate->y);

        $location = new Location();
        $location->setLanguage('de');
        $location->setName($startDate->location);

        $eventEvent = new CalendarEvent();
        $eventEvent->setStart(new DateTime($startDate->date))
          ->setEnd(new DateTime($event->getLastEventDate()->date))
          ->setSummary($event->name)
          ->setSequence(1)
          ->setStatus('CONFIRMED')
          ->setCreated(new DateTime($event->created_at))
          ->setOrganizer($appOrganizer)
          ->setUid('allEvent' . $event->id);

        if ($startDate->x != -199 && $startDate->y != -199) {
          $geo = new Geo();
          $geo->setLatitude($startDate->x);
          $geo->setLongitude($startDate->y);

          $eventEvent->setGeo($geo);
        }

        if ($event->description != null) {
          if (strlen($event->description) > 2) {
            $eventEvent->setDescription($event->description);
          }
        }

        if ($startDate->location != null) {
          if (strlen($startDate->location) > 0) {
            $location = new Location();
            $location->setLanguage('de');
            $location->setName($startDate->location);
            $eventEvent->addLocation($location);
          }
        }

        $calendar->addEvent($eventEvent);
      }
    }

    $calendarExport->addCalendar($calendar);
    $stream = $calendarExport->getStream();
    Cache::put(CalendarController::$completeCalendarCacheKey, $stream, 60 * 60 * 4);

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="calendar.ics"');
    echo $stream;
  }
}
