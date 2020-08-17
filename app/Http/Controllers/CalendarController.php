<?php

namespace App\Http\Controllers;

use App\Logging;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\Event;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Repositories\User\UserToken\IUserTokenRepository;
use DateTime;
use DateTimeZone;
use Exception;
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

class CalendarController extends Controller
{

  protected $userTokenRepository = null;
  protected $settingRepository = null;
  protected $userRepository = null;
  protected $userSettingRepository = null;
  protected $eventDateRepository = null;
  protected $movieRepository = null;
  protected $eventRepository = null;

  public function __construct(IUserTokenRepository $userTokenRepository, ISettingRepository $settingRepository,
                              IUserRepository $userRepository, IUserSettingRepository $userSettingRepository,
                              IEventDateRepository $eventDateRepository, IMovieRepository $movieRepository,
                              IEventRepository $eventRepository) {
    $this->userTokenRepository = $userTokenRepository;
    $this->settingRepository = $settingRepository;
    $this->userRepository = $userRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->eventDateRepository = $eventDateRepository;
    $this->movieRepository = $movieRepository;
    $this->eventRepository = $eventRepository;
  }

  /**
   * @param string $token
   * @return string
   * @throws CalendarEventException
   * @throws Exception
   */
  public function getCalendarOf($token) {
    $tokenObject = $this->userTokenRepository->getUserTokenByTokenAndPurpose($token, 'calendar');
    if ($tokenObject == null) {
      return response()->json(['msg' => 'Provided token is incorrect'], 401);
    }

    $user = $tokenObject->user();

    $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
    $calendar = new Calendar();
    $calendar->setTimezone(new DateTimeZone('Europe/Vienna'));
    $calendar->setProdId('datepoll-personal-calendar');


    $appOrganizer = new Organizer(new Formatter());
    $appOrganizer->setValue(env('MAIL_FROM_ADDRESS'))
                 ->setName($this->settingRepository->getCommunityName())
                 ->setLanguage('de');

    if ($this->settingRepository->getCinemaEnabled() && $this->userSettingRepository->getShowMoviesInCalendarForUser($user)) {
      /* -------- Movie booking specific calendar -------------*/
      $movies = array();
      $movieBookings = MoviesBooking::where('user_id', $user->id)
                                    ->get();
      foreach ($movieBookings as $movieBooking) {
        $movie = $movieBooking->movie();
        $movie->booked_tickets_for_yourself = $movieBooking->amount;
        $movies[] = $movie;
      }

      foreach ($movies as $movie) {
        $uid = app('hash')->make($movie->name . $movie->created_at);

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
                   ->setDescription('Reservierte Karten: ' . $movie->booked_tickets_for_yourself)
                   ->setUrl($movie->trailerLink)
                   ->setGeo($geo)
                   ->setSequence(1)
                   ->setStatus('CONFIRMED')
                   ->setCreated(new DateTime($movie->created_at))
                   ->addLocation($location)
                   ->setUid($uid);

        $worker = $movie->worker();
        if ($worker != null) {
          $name = $worker->firstname . ' ' . $worker->surname;

          $organizer = new Organizer(new Formatter());
          $organizer->setValue($worker->email)
                    ->setName($name)
                    ->setLanguage('de');
          $movieEvent->setOrganizer($organizer);
        } else {
          $movieEvent->setOrganizer($appOrganizer);
        }

        $calendar->addEvent($movieEvent);
      }

      /* -------- Movie worker specific calendar -------------*/
      $moviesWorker = $user->workerMovies();
      foreach ($moviesWorker as $movie) {
        $movieAlreadyInCalendar = false;
        foreach ($movies as $movieB) {
          if ($movieB->id === $movie->id) {
            $movieAlreadyInCalendar = true;
            break;
          }
        }

        if (!$movieAlreadyInCalendar) {
          $uid = app('hash')->make($movie->name . $movie->created_at);

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
                     ->setUid($uid);

          $worker = $movie->worker();
          if ($worker != null) {
            $name = $worker->firstname . ' ' . $worker->surname;

            $organizer = new Organizer(new Formatter());
            $organizer->setValue($worker->email)
                      ->setName($name)
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

      $events = array();
      foreach ($eventIds as $eventId) {
        $events[] = Event::find($eventId->id);
      }

      foreach ($events as $event) {
        $uid = app('hash')->make($event->name . $event->created_at);
        $startDate = $this->eventDateRepository->getFirstEventDateForEvent($event);

        $eventEvent = new CalendarEvent();
        $eventEvent->setStart(new DateTime($startDate->date))
                   ->setEnd(new DateTime($this->eventDateRepository->getLastEventDateForEvent($event)->date))
                   ->setSummary($event->name)
                   ->setSequence(1)
                   ->setStatus('CONFIRMED')
                   ->setCreated(new DateTime($event->created_at))
                   ->setOrganizer($appOrganizer)
                   ->setUid($uid);

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
          $d = date_parse_from_format("Y-m-d", $user->birthday);
          if ($d["month"] == date('n')) {
            $uid = app('hash')->make($user->id . $user->created_at);
            $birthdayEvent = new CalendarEvent();
            $birthdayEvent->setStart($this->updateDate($user->birthday))
                          ->setEnd($this->updateDate($user->birthday))
                          ->setAllDay(true)
                          ->setSequence(1)
                          ->setStatus('CONFIRMED')
                          ->setCreated(new DateTime($user->created_at))
                          ->setOrganizer($appOrganizer)
                          ->setSummary($user->firstname . ' ' . $user->surname . '\'s Geburtstag')
                          ->setUid($uid);

            $calendar->addEvent($birthdayEvent);
          }
        }
      }
    }

    $calendarExport->addCalendar($calendar);

    Logging::info("getCalendarOf", "User | " . $user->id . " | ICS personal calendar request");

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="cal.ics"');

    echo $calendarExport->getStream();
  }

  /**
   * @param String $dateString
   * @return DateTime
   * @throws Exception
   */
  private function updateDate($dateString){
    $suppliedDate = new DateTime($dateString);
    $currentYear = (int)(new DateTime())->format('Y');
    return (new DateTime())->setDate($currentYear, (int)$suppliedDate->format('m'), (int)$suppliedDate->format('d'));
  }

  /**
   * @return string
   * @throws CalendarEventException
   * @throws Exception
   */
  public function getCompleteCalendar() {
    $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
    $calendar = new Calendar();
    $calendar->setProdId('datepoll-complete-calendar');

    $appOrganizer = new Organizer(new Formatter());
    $appOrganizer->setValue(env('MAIL_FROM_ADDRESS'))
                 ->setName($this->settingRepository->getCommunityName())
                 ->setLanguage('de');

    if ($this->settingRepository->getCinemaEnabled()) {
      foreach ($this->movieRepository->getAllMoviesOrderedByDate() as $movie) {
        $uid = app('hash')->make($movie->name . $movie->created_at . $event->updated_at);

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
                   ->setUid($uid);

        $calendar->addEvent($movieEvent);
      }
    }

    if ($this->settingRepository->getEventsEnabled()) {
      foreach ($this->eventRepository->getAllEventsOrderedByDate() as $event) {
        $uid = app('hash')->make($event->name . $event->created_at . $event->updated_at);
        $startDate = $this->eventDateRepository->getFirstEventDateForEvent($event);

        $geo = new Geo();
        $geo->setLatitude($startDate->x);
        $geo->setLongitude($startDate->y);

        $location = new Location();
        $location->setLanguage('de');
        $location->setName($startDate->location);


        $eventEvent = new CalendarEvent();
        $eventEvent->setStart(new DateTime($startDate->date))
                   ->setEnd(new DateTime($this->eventDateRepository->getLastEventDateForEvent($event)->date))
                   ->setSummary($event->name)
                   ->setSequence(1)
                   ->setStatus('CONFIRMED')
                   ->setCreated(new DateTime($event->created_at))
                   ->setOrganizer($appOrganizer)
                   ->setUid($uid);

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

    Logging::info("getCompleteCalendar", "ICS complete calendar request");
    return $calendarExport->getStream();
  }
}
