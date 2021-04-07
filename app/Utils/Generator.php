<?php

namespace App\Utils;

use App\Logging;
use Exception;
use JetBrains\PhpStorm\Pure;

abstract class Generator {

  /**
   * @param int $length
   * @return string
   * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
   */
  #[Pure]
  public static function getRandomMixedNumberAndABCToken(int $length = 1): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomToken = '';
    for ($i = 0; $i < $length; $i++) {
      try {
        $randomToken .= $characters[random_int(0, $charactersLength - 1)];
      } catch (Exception) {
        Logging::error('getRandomMixedNumberAndABCToken', 'Could not gather enough data to generate random string.');
      }
    }

    return $randomToken;
  }

  /**
   * @return int
   */
  public static function getRandom6DigitNumber(): int {
    try {
      return random_int(100000, 999999);
    } catch (Exception) {
      Logging::error('getRandomMixedNumberAndABCToken', 'Could not gather enough data to generate random string.');
    }
  }
}
