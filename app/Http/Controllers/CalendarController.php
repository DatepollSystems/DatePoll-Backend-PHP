<?php

namespace App\Http\Controllers;

use App\Models\Cinema\MoviesBooking;
use App\Models\User\UserToken;
use DateTime;
use Exception;
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
      $movieEvent->setStart(new DateTime($movie->date . 'T20:30:00'))->setEnd(new DateTime($movie->date . 'T23:59:59'))->setSummary($movie->name)->setDescription('Reservierte Karten: ' . $movie->bookedTickets)->setUrl($movie->trailerLink)->setGeo($geo)->addLocation($location)->setUid($movie->id);

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
        $movieEvent->setStart(new \DateTime($movie->date . 'T20:30:00'))->setEnd(new \DateTime($movie->date . 'T23:59:59'))->setSummary($movie->name)->setDescription('Reservierte Karten: ' . $movie->bookedTickets)->setUrl($movie->trailerLink)->setGeo($geo)->addLocation($location)->setUid($movie->id);

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

    $calendarExport->addCalendar($calendar);

    return $calendarExport->getStream();
  }

  /**
   * @return string
   */
  public function getCompleteCalendar() {
    return "Coming soon";
  }
}
