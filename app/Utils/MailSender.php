<?php

namespace App\Utils;

use App\Jobs\SendEmailJob;
use App\Mail\ADatePollMailable;
use DateTime;
use Laravel\Lumen\Bus\PendingDispatch;

abstract class MailSender {
  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param string $queue
   * @return PendingDispatch
   */
  private static function sendEmailOnQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList, string $queue): PendingDispatch {
    if (! ArrayHelper::isArray($receiverEmailAddressList)) {
      $receiverEmailAddressList = [$receiverEmailAddressList];
    }

    return QueueHandler::addJobToQueue(new SendEmailJob($datePollMailable, $receiverEmailAddressList), $queue);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @return PendingDispatch
   */
  public static function sendEmailOnLowQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList): PendingDispatch {
    return self::sendEmailOnQueue($datePollMailable, $receiverEmailAddressList, QueueHandler::$QUEUE_LOW);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @return PendingDispatch
   */
  public static function sendEmailOnDefaultQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList): PendingDispatch {
    return self::sendEmailOnQueue($datePollMailable, $receiverEmailAddressList, QueueHandler::$QUEUE_DEFAULT);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @return PendingDispatch
   */
  public static function sendEmailOnHighQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList): PendingDispatch {
    return self::sendEmailOnQueue($datePollMailable, $receiverEmailAddressList, QueueHandler::$QUEUE_HIGH);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @param string $queue
   * @return mixed
   */
  private static function sendDelayedEmailOnQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList, DateTime $time, string $queue) {
    if (! ArrayHelper::isArray($receiverEmailAddressList)) {
      $receiverEmailAddressList = [$receiverEmailAddressList];
    }

    return QueueHandler::addDelayedJobToQueue(new SendEmailJob($datePollMailable, $receiverEmailAddressList), $time, $queue);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @return mixed
   */
  public static function sendDelayedEmailOnLowQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList, DateTime $time) {
    return self::sendDelayedEmailOnQueue($datePollMailable, $receiverEmailAddressList, $time, QueueHandler::$QUEUE_LOW);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @return mixed
   */
  public static function sendDelayedEmailOnDefaultQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList, DateTime $time) {
    return self::sendDelayedEmailOnQueue($datePollMailable, $receiverEmailAddressList, $time, QueueHandler::$QUEUE_DEFAULT);
  }

  /**
   * @param ADatePollMailable $datePollMailable
   * @param string|string[] $receiverEmailAddressList
   * @param DateTime $time
   * @return mixed
   */
  public static function sendDelayedEmailOnHighQueue(ADatePollMailable $datePollMailable, $receiverEmailAddressList, DateTime $time) {
    return self::sendDelayedEmailOnQueue($datePollMailable, $receiverEmailAddressList, $time, QueueHandler::$QUEUE_HIGH);
  }
}
