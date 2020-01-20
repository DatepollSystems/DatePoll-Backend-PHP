<?php
namespace App;

class Permissions {
  public static $ROOT_ADMINISTRATION = 'root.administration';

  public static $PERMISSION_ADMINISTRATION = 'permissions.*';

  public static $CINEMA_ADMINISTRATION = 'cinema.*';

  public static $EVENTS_ADMINISTRATION = 'events.*';
  public static $EVENTS_VIEW_DETAILS = 'events.details';

  public static $FILES_ADMINISTRATION = 'files.*';

  public static $MANAGEMENT_ADMINISTRATION = 'management.*';

  public static $SETTINGS_ADMINISTRATION = 'settings.*';

  public static $SYSTEM_ADMINISTRATION = 'system.*';
  public static $SYSTEM_JOBS_ADMINISTRATION = 'system.jobs.*';
  public static $SYSTEM_LOGS_ADMINISTRATION = 'system.logs.*';
}