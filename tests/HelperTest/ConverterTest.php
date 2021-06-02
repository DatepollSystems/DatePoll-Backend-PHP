<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use App\Utils\Converter;

class ConverterTest extends TestCase {
  public function testStringToBoolean(): void {
    self::assertTrue(Converter::stringToBoolean('true'));
    self::assertFalse(Converter::stringToBoolean('false'));
    self::assertFalse(Converter::stringToBoolean('irgendwas'));
  }

  public function testIntegerToBoolean(): void {
    self::assertTrue(Converter::integerToBoolean(1));
    self::assertFalse(Converter::integerToBoolean(0));
  }

  public function testBooleanToString(): void {
    self::assertEquals('true', Converter::booleanToString(true));
    self::assertEquals('false', Converter::booleanToString(false));
  }

  public function testStringToInteger(): void {
    self::assertEquals(1, Converter::stringToInteger('1'));
    self::assertEquals(-21, Converter::stringToInteger('-21'));
    self::assertEquals(2000001012, Converter::stringToInteger('2000001012'));
  }

  public function testIntegerToString(): void {
    self::assertEquals('1', Converter::integerToString(1));
    self::assertEquals('-21', Converter::integerToString(-21));
    self::assertEquals('2000001012', Converter::integerToString(2000001012));
  }
}
