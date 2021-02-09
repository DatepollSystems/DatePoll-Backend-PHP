<?php

namespace App\Utils;

use Exception;
use JetBrains\PhpStorm\Pure;

abstract class Generator {

  /**
   * @param int $length
   * @return string
   * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
   * @throws Exception
   */
  #[Pure]
  public static function getRandomMixedNumberAndABCToken(int $length = 1): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomToken = '';
    for ($i = 0; $i < $length; $i++) {
      $randomToken .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomToken;
  }

  /**
   * @return int
   * @throws Exception
   */
  public static function getRandom6DigitNumber(): int {
    return random_int(100000, 999999);
  }
}
