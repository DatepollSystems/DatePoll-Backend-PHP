<?php

namespace App\Utils;

use App\Jobs\Job;
use DateTime;
use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Bus\PendingDispatch;

abstract class QueueHelper {
  public static string $QUEUE_LOW = 'low';
  public static string $QUEUE_DEFAULT = 'default';
  public static string $QUEUE_HIGH = 'high';

  /**
   * @param Job $job
   * @param string $queue . Please use <code>QueueHelper::$QUEUE_LOW</code>, <code>QueueHelper::$QUEUE_DEFAULT</code>
   *   or <code>QueueHelper::$QUEUE_HIGH</code>.
   * @return PendingDispatch
   */
  public static function addJobToQueue(Job $job, string $queue = 'default'): PendingDispatch {
    return dispatch($job)->onQueue($queue);
  }

  /**
   * @param Job $job
   * @return PendingDispatch
   */
  public static function addJobToLowQueue(Job $job): PendingDispatch {
    return self::addJobToQueue($job, self::$QUEUE_LOW);
  }

  /**
   * @param Job $job
   * @return PendingDispatch
   */
  public static function addJobToDefaultQueue(Job $job): PendingDispatch {
    return self::addJobToQueue($job, self::$QUEUE_DEFAULT);
  }

  /**
   * @param Job $job
   * @return PendingDispatch
   */
  public static function addJobToHighQueue(Job $job): PendingDispatch {
    return self::addJobToQueue($job, self::$QUEUE_HIGH);
  }

  /**
   * @param Job $job
   * @param DateTime $time
   * @param string $queue Please use <code>QueueHelper::$QUEUE_LOW</code>, <code>QueueHelper::$QUEUE_DEFAULT</code>
   *   or <code>QueueHelper::$QUEUE_HIGH</code>.
   * @return void
   */
  public static function addDelayedJobToQueue(Job $job, DateTime $time, string $queue = 'default') {
    Queue::later($time, $job, null, $queue);
  }

  /**
   * @param Job $job
   * @param DateTime $time
   * @return void
   */
  public static function addDelayedJobToLowQueue(Job $job, DateTime $time) {
    self::addDelayedJobToQueue($job, $time, self::$QUEUE_LOW);
  }

  /**
   * @param Job $job
   * @param DateTime $time
   * @return void
   */
  public static function addDelayedJobToDefaultQueue(Job $job, DateTime $time) {
    self::addDelayedJobToQueue($job, $time, self::$QUEUE_DEFAULT);
  }

  /**
   * @param Job $job
   * @param DateTime $time
   * @return void
   */
  public static function addDelayedJobToHighQueue(Job $job, DateTime $time) {
    self::addDelayedJobToQueue($job, $time, self::$QUEUE_HIGH);
  }
}
