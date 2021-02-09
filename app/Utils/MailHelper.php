<?php

namespace App\Utils;

use App\Jobs\SendEmailJob;
use App\Mail\ADatePollMailable;
use DateTime;
use Laravel\Lumen\Bus\PendingDispatch;

abstract class MailHelper {
  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param string $queue
   * @return PendingDispatch
   */
  private static function sendEmailOnQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList, string $queue): PendingDispatch {
    if (! ArrayHelper::isArray($receiverEmailAddressList)) {
      $receiverEmailAddressList = [$receiverEmailAddressList];
    }

    return QueueHelper::addJobToQueue(new SendEmailJob($datePollMailable, $receiverEmailAddressList), $queue);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @return PendingDispatch
   */
  public static function sendEmailOnLowQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList): PendingDispatch {
    return self::sendEmailOnQueue($datePollMailable, $receiverEmailAddressList, QueueHelper::$QUEUE_LOW);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @return PendingDispatch
   */
  public static function sendEmailOnDefaultQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList): PendingDispatch {
    return self::sendEmailOnQueue($datePollMailable, $receiverEmailAddressList, QueueHelper::$QUEUE_DEFAULT);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @return PendingDispatch
   */
  public static function sendEmailOnHighQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList): PendingDispatch {
    return self::sendEmailOnQueue($datePollMailable, $receiverEmailAddressList, QueueHelper::$QUEUE_HIGH);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @param string $queue
   * @return void
   */
  private static function sendDelayedEmailOnQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList, DateTime $time, string $queue): void {
    if (! ArrayHelper::isArray($receiverEmailAddressList)) {
      $receiverEmailAddressList = [$receiverEmailAddressList];
    }

    QueueHelper::addDelayedJobToQueue(new SendEmailJob($datePollMailable, $receiverEmailAddressList), $time, $queue);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @return void
   */
  public static function sendDelayedEmailOnLowQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList, DateTime $time): void {
    self::sendDelayedEmailOnQueue($datePollMailable, $receiverEmailAddressList, $time, QueueHelper::$QUEUE_LOW);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @return void
   */
  public static function sendDelayedEmailOnDefaultQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList, DateTime $time): void {
    self::sendDelayedEmailOnQueue($datePollMailable, $receiverEmailAddressList, $time, QueueHelper::$QUEUE_DEFAULT);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @return void
   */
  public static function sendDelayedEmailOnHighQueue(ADatePollMailable $datePollMailable, string|array $receiverEmailAddressList, DateTime $time): void {
    self::sendDelayedEmailOnQueue($datePollMailable, $receiverEmailAddressList, $time, QueueHelper::$QUEUE_HIGH);
  }
}
