<?php

namespace App\Http\Controllers;

use App\Logging;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\Event;
use App\Models\User\UserToken;
use DateTime;
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
  /**
   * @param $token
   * @return string
   * @throws CalendarEventException
   * @throws Exception
   */
  public function getCalendarOf($token) {
    $tokenObject = UserToken::where('token', $token)->where('purpose', 'calendar')->first();
    if ($tokenObject == null) {
      return response()->json(['msg' => 'Provided token is incorrect'], 401);
    }

    $user = $tokenObject->user();

    $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
    $calendar = new Calendar();
    $calendar->setProdId('datepoll-calendar');

    $calendarEventId = 1;

    /* -------- Movie booking specific calendar -------------*/
    $movies = array();
    $movieBookings = MoviesBooking::where('user_id', $user->id)->get();
    foreach ($movieBookings as $movieBooking) {
      $movies[] = $movieBooking->movie();
    }

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
                 ->setDescription('Reservierte Karten: ' . $movie->bookedTickets)
                 ->setUrl($movie->trailerLink)
                 ->setGeo($geo)
                 ->addLocation($location)
                 ->setUid($calendarEventId);
      $calendarEventId++;

      $worker = $movie->worker();
      if ($worker != null) {
        $name = $worker->firstname . ' ' . $worker->surname;

        $organizer = new Organizer(new Formatter());
        $organizer->setValue($worker->email)->setName($name)->setLanguage('de');
        $movieEvent->setOrganizer($organizer);
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
        $geo = new Geo();
        $geo->setLatitude(48.643865);
        $geo->setLongitude(15.814679);

        $location = new Location();
        $location->setLanguage('de');
        $location->setName('Kanzlerturm Wiese Eggenburg');

        $movieEvent = new CalendarEvent();
        $movieEvent->setStart(new DateTime($movie->date . 'T20:30:00'))
                   ->setEnd(new DateTime($movie->date . 'T23:59:59'))
                   ->setSummary($movie->name)//->setDescription('Reservierte Karten: ' . $movie->bookedTickets)
                   ->setUrl($movie->trailerLink)
                   ->setGeo($geo)
                   ->addLocation($location)
                   ->setUid($calendarEventId);
        $calendarEventId++;

        $worker = $movie->worker();
        if ($worker != null) {
          $name = $worker->firstname . ' ' . $worker->surname;

          $organizer = new Organizer(new Formatter());
          $organizer->setValue($worker->email)->setName($name)->setLanguage('de');
          $movieEvent->setOrganizer($organizer);
        }

        $calendar->addEvent($movieEvent);
      }
    }

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
//      $geo = new Geo();
//      $geo->setLatitude(48.643865);
//      $geo->setLongitude(15.814679);

      $location = new Location();
      $location->setLanguage('de');
      $location->setName($event->location);

      $eventEvent = new CalendarEvent();
      $eventEvent->setStart(new DateTime($event->startDate))
                 ->setEnd(new DateTime($event->endDate))
                 ->setSummary($event->name)
                 ->setDescription($event->description)//       ->setUrl($movie->trailerLink)
//        ->setGeo($geo)
                 ->addLocation($location)
                 ->setUid($calendarEventId);
      $calendarEventId++;

      $calendar->addEvent($eventEvent);
    }

    $calendarExport->addCalendar($calendar);

    Logging::info("getCalendarOf", "User | " . $user->id . " | ICS personal calendar request");
    return $calendarExport->getStream();
  }

  /**
   * @return string
   */
  public function getCompleteCalendar() {
    return "Coming soon";
  }
}
