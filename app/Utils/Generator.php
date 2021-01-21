<?php

namespace App\Utils;

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
      $randomToken .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomToken;
  }

  /**
   * @return int
   */
  #[Pure]
  public static function getRandom6DigitNumber(): int {
    return rand(100000, 999999);
  }
}
