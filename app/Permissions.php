<?php
namespace App;

class Permissions {
  public static string $ROOT_ADMINISTRATION = 'root.administration';

  public static string $CINEMA_ADMINISTRATION = 'cinema.*';

  public static string $EVENTS_ADMINISTRATION = 'events.*';
  public static string $EVENTS_VIEW_DETAILS = 'events.details';

  public static string $BROADCASTS_ADMINISTRATION = 'broadcasts.*';
  public static string $BROADCASTS_DELETE_EXTRA = 'broadcasts.delete';

  public static string $SEAT_RESERVATION_ADMINISTRATION = 'seatReservation.*';

  public static string $FILES_ADMINISTRATION = 'files.*';

  public static string $MANAGEMENT_ADMINISTRATION = 'management.*';
  public static string $MANAGEMENT_USER_VIEW = 'management.user.view';
  public static string $MANAGEMENT_EXTRA_USER_PERMISSIONS = 'management.user.permissions';
  public static string $MANAGEMENT_EXTRA_USER_DELETE = 'management.user.delete';

  public static string $SETTINGS_ADMINISTRATION = 'settings.*';

  public static string $SYSTEM_ADMINISTRATION = 'system.*';
  public static string $SYSTEM_JOBS_ADMINISTRATION = 'system.jobs.*';
  public static string $SYSTEM_LOGS_ADMINISTRATION = 'system.logs.*';
}
