<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use App\Utils\StringHelper;

class StringHelperTest extends TestCase {
  public function testNotNull(): void {
    $helperFunction = static function ($var) {
      return StringHelper::notNull($var);
    };
    self::assertTrue($helperFunction('test'));
    self::assertTrue($helperFunction(')'));
    self::assertTrue($helperFunction(''));
    self::assertTrue($helperFunction(' '));
    self::assertTrue($helperFunction(1));
    self::assertTrue($helperFunction(1.5));
    self::assertFalse($helperFunction(null));
  }

  public function testNull(): void {
    $helperFunction = static function ($var) {
      return StringHelper::null($var);
    };
    self::assertFalse($helperFunction('test'));
    self::assertFalse($helperFunction(')'));
    self::assertFalse($helperFunction(''));
    self::assertFalse($helperFunction(' '));
    self::assertFalse($helperFunction(1));
    self::assertFalse($helperFunction(1.5));
    self::assertTrue($helperFunction(null));
  }

  public function testTrim(): void {
    $helperFunction = static function ($var) {
      return StringHelper::trim($var);
    };
    self::assertEquals('test test', $helperFunction('    test test'));
    self::assertNotEquals('test test', $helperFunction('    test  test'));
  }

  public function testNotNullAndEmpty(): void {
    $helperFunction = static function ($var) {
      return StringHelper::notNullAndEmpty($var);
    };
    self::assertTrue($helperFunction('test'));
    self::assertTrue($helperFunction(')'));
    self::assertTrue($helperFunction(1));
    self::assertTrue($helperFunction(1.5));
    self::assertFalse($helperFunction(''));
    self::assertFalse($helperFunction(' '));
    self::assertFalse($helperFunction(null));
  }

  public function testNullAndEmpty(): void {
    $helperFunction = static function ($var) {
      return StringHelper::nullAndEmpty($var);
    };
    self::assertFalse($helperFunction('test'));
    self::assertFalse($helperFunction(')'));
    self::assertFalse($helperFunction(1));
    self::assertFalse($helperFunction(1.5));
    self::assertTrue($helperFunction(''));
    self::assertTrue($helperFunction(' '));
    self::assertTrue($helperFunction(null));
  }

  public function testContains(): void {
    $helperFunction = static function ($var1, $var2) {
      return StringHelper::contains($var1, $var2);
    };
    self::assertTrue($helperFunction('Hello my love', 'my'));
    self::assertTrue($helperFunction('Hello my love', 'y'));
    self::assertTrue($helperFunction('Hello my love', 'my '));
    self::assertTrue($helperFunction('Hello my love', 'o my lo'));
    self::assertTrue($helperFunction('Hello my love', 've'));
    self::assertTrue($helperFunction('Hello my love', 'Hello my love'));
    self::assertTrue($helperFunction(null, null));
    self::assertTrue($helperFunction(' ', ' '));
    self::assertTrue($helperFunction('  ', ' '));
    self::assertFalse($helperFunction(' ', '  '));
    self::assertFalse($helperFunction('Hello my love', 'my  '));
    self::assertFalse($helperFunction('Hello my love', 'Hello my love '));
    self::assertFalse($helperFunction('Hello my love', 'AAAAAAA'));
    self::assertFalse($helperFunction('Hello my love', 'null'));
    self::assertFalse($helperFunction('Hello my love', null));
    self::assertFalse($helperFunction(null, 'Hello my love'));
    self::assertFalse($helperFunction('Hello', 'Hello my love'));
  }

  public function testLength(): void {
    self::assertEquals(4, StringHelper::length('test'));
    self::assertEquals(0, StringHelper::length(' '));
    self::assertEquals(0, StringHelper::length('  '));
    self::assertEquals(0, StringHelper::length(null));
    self::assertEquals(1, StringHelper::length(1));
    self::assertEquals(3, StringHelper::length(1.2));
    self::assertEquals(6, StringHelper::length('_1_3_t'));
  }

  public function testLengthWithoutTrim(): void {
    self::assertEquals(4, StringHelper::lengthWithoutTrim('test'));
    self::assertEquals(1, StringHelper::lengthWithoutTrim(' '));
    self::assertEquals(2, StringHelper::lengthWithoutTrim('  '));
    self::assertEquals(0, StringHelper::lengthWithoutTrim(null));
    self::assertEquals(1, StringHelper::lengthWithoutTrim(1));
    self::assertEquals(3, StringHelper::lengthWithoutTrim(1.2));
    self::assertEquals(6, StringHelper::lengthWithoutTrim('_1_3_t'));
  }

  public function testCountSubstring(): void {
    $helperFunction = static function ($var1, $var2) {
      return StringHelper::countSubstring($var1, $var2);
    };
    self::assertEquals(1, $helperFunction('Hello my love', 'my'));
    self::assertEquals(1, $helperFunction('Hello my love', 'y'));
    self::assertEquals(1, $helperFunction('Hello my love', 'my '));
    self::assertEquals(1, $helperFunction('', ''));
    self::assertEquals(1, $helperFunction(' ', ''));
    self::assertEquals(1, $helperFunction('Test', ''));
    self::assertEquals(1, $helperFunction('Hello my love', 'o my lo'));
    self::assertEquals(1, $helperFunction('Hello my love', 'll'));
    self::assertEquals(1, $helperFunction(null, null));
    self::assertEquals(2, $helperFunction('Hello my love', 'o'));
    self::assertEquals(2, $helperFunction('  ', ' '));
    self::assertEquals(3, $helperFunction('Hello my love', 'l'));
    self::assertEquals(3, $helperFunction('Hello my love', 'l'));
    self::assertEquals(0, $helperFunction('Hello my love', '  '));
    self::assertEquals(0, $helperFunction('Hello my love', 'd'));
    self::assertEquals(0, $helperFunction('Hello my love', 'lovee'));
    self::assertEquals(0, $helperFunction(null, 'test'));
    self::assertEquals(0, $helperFunction('test', null));
  }

  public function testToLowerCase(): void {
    $helperFunction = static function ($var1) {
      return StringHelper::toLowerCase($var1);
    };
    self::assertEquals('alles ist klein', $helperFunction('AllEs iST KLEIN'));
    self::assertEquals('klein__', $helperFunction('KLEIN__'));
    self::assertEquals('__', $helperFunction('__'));
    self::assertEquals('11', $helperFunction('11'));
    self::assertEquals(null, $helperFunction(null));
    self::assertEquals(' ', $helperFunction(' '));
    self::assertEquals('  ', $helperFunction('  '));
  }

  public function testToLowerCaseWithTrim(): void {
    $helperFunction = static function ($var1) {
      return StringHelper::toLowerCaseWithTrim($var1);
    };
    self::assertEquals('alles ist klein', $helperFunction('AllEs iST KLEIN'));
    self::assertEquals('klein__', $helperFunction('KLEIN__'));
    self::assertEquals('__', $helperFunction('__'));
    self::assertEquals('11', $helperFunction('11'));
    self::assertEquals(null, $helperFunction(null));
    self::assertEquals('', $helperFunction(' '));
    self::assertEquals('', $helperFunction('  '));
  }

  public function testToUpperCase(): void {
    $helperFunction = static function ($var1) {
      return StringHelper::toUpperCase($var1);
    };
    self::assertEquals('ALLES IST KLEIN', $helperFunction('AllEs iST KLEIN'));
    self::assertEquals('KLEIN__', $helperFunction('klein__'));
    self::assertEquals('__', $helperFunction('__'));
    self::assertEquals('11', $helperFunction('11'));
    self::assertEquals(null, $helperFunction(null));
    self::assertEquals(' ', $helperFunction(' '));
    self::assertEquals('  ', $helperFunction('  '));
  }

  public function testToUpperCaseWithTrim(): void {
    $helperFunction = static function ($var1) {
      return StringHelper::toUpperCaseWithTrim($var1);
    };
    self::assertEquals('ALLES IST KLEIN', $helperFunction('AllEs iST KLEIN'));
    self::assertEquals('KLEIN__', $helperFunction('klein__'));
    self::assertEquals('__', $helperFunction('__'));
    self::assertEquals('11', $helperFunction('11'));
    self::assertEquals(null, $helperFunction(null));
    self::assertEquals('', $helperFunction(' '));
    self::assertEquals('', $helperFunction('  '));
  }

  public function testEquals(): void {
    $helperFunction = static function ($var1, $var2) {
      return StringHelper::equals($var1, $var2);
    };
    self::assertTrue($helperFunction('asdfGHJKL', 'asdfghjkl'));
    self::assertTrue($helperFunction('asdfGHJKL', 'asdfGHJKL'));
    self::assertTrue($helperFunction('AAAAAAA', 'aaaaaaa'));
    self::assertTrue($helperFunction('test', 'test'));
    self::assertTrue($helperFunction('Test__1', 'Test__1'));
    self::assertTrue($helperFunction('__', '__'));
    self::assertTrue($helperFunction('12', '12'));
    self::assertTrue($helperFunction(' ', ' '));
    self::assertTrue($helperFunction('  ', '  '));
    self::assertTrue($helperFunction('My love of ', 'My love of '));
    self::assertTrue($helperFunction(null, null));
    self::assertFalse($helperFunction(null, 'test'));
    self::assertFalse($helperFunction('test', null));
    self::assertFalse($helperFunction('AAAAAAA', 'bbbbbbb'));
    self::assertFalse($helperFunction('test', 'keintest'));
    self::assertFalse($helperFunction('test', '1234'));
    self::assertFalse($helperFunction('1234', '12345'));
    self::assertFalse($helperFunction('5', '6'));
    self::assertFalse($helperFunction('!', '?'));
  }

  public function testNotEquals(): void {
    $helperFunction = static function ($var1, $var2) {
      return StringHelper::notEquals($var1, $var2);
    };
    self::assertFalse($helperFunction('asdfGHJKL', 'asdfghjkl'));
    self::assertFalse($helperFunction('asdfGHJKL', 'asdfGHJKL'));
    self::assertFalse($helperFunction('AAAAAAA', 'aaaaaaa'));
    self::assertFalse($helperFunction('test', 'test'));
    self::assertFalse($helperFunction('Test__1', 'Test__1'));
    self::assertFalse($helperFunction('__', '__'));
    self::assertFalse($helperFunction('12', '12'));
    self::assertFalse($helperFunction(' ', ' '));
    self::assertFalse($helperFunction('  ', '  '));
    self::assertFalse($helperFunction('My love of ', 'My love of '));
    self::assertFalse($helperFunction(null, null));
    self::assertTrue($helperFunction(null, 'test'));
    self::assertTrue($helperFunction('test', null));
    self::assertTrue($helperFunction('AAAAAAA', 'bbbbbbb'));
    self::assertTrue($helperFunction('test', 'keintest'));
    self::assertTrue($helperFunction('test', '1234'));
    self::assertTrue($helperFunction('1234', '12345'));
    self::assertTrue($helperFunction('5', '6'));
    self::assertTrue($helperFunction('!', '?'));
  }

  public function testEqualsCaseSensitive(): void {
    $helperFunction = static function ($var1, $var2) {
      return StringHelper::equalsCaseSensitive($var1, $var2);
    };
    self::assertTrue($helperFunction('asdfGHJKL', 'asdfGHJKL'));
    self::assertFalse($helperFunction('asdfGHJKL', 'asdfghjkl'));
    self::assertTrue($helperFunction('AAAAAAA', 'AAAAAAA'));
    self::assertFalse($helperFunction('AAAAAAA', 'aaaaaaa'));
    self::assertTrue($helperFunction('test', 'test'));
    self::assertTrue($helperFunction('Test__1', 'Test__1'));
    self::assertTrue($helperFunction('__', '__'));
    self::assertTrue($helperFunction('12', '12'));
    self::assertTrue($helperFunction(' ', ' '));
    self::assertTrue($helperFunction('  ', '  '));
    self::assertTrue($helperFunction('My love of ', 'My love of '));
    self::assertTrue($helperFunction(null, null));
    self::assertFalse($helperFunction(null, 'test'));
    self::assertFalse($helperFunction('test', null));
    self::assertFalse($helperFunction('AAAAAAA', 'bbbbbbb'));
    self::assertFalse($helperFunction('test', 'keintest'));
    self::assertFalse($helperFunction('test', '1234'));
    self::assertFalse($helperFunction('1234', '12345'));
    self::assertFalse($helperFunction('5', '6'));
    self::assertFalse($helperFunction('!', '?'));
  }

  public function testStartsWithCharacter(): void {
    $helperFunction = static function ($var1, $var2) {
      return StringHelper::startsWithCharacter($var1, $var2);
    };
    self::assertTrue($helperFunction('Das ist ein Test', 'Das ist '));
    self::assertTrue($helperFunction('Das ist ein Test', 'Das ist'));
    self::assertFalse($helperFunction('Das ist ein Test', 'Das ist ein Tester'));
    self::assertFalse($helperFunction('Das ist ein Test', 'bbaldsfasr'));
    self::assertFalse($helperFunction('Das ist ein Test', 'Das ______asdafsdf'));
    self::assertTrue($helperFunction('1234', '1234'));
    self::assertFalse($helperFunction('1234', '12345'));
    self::assertTrue($helperFunction(' ', ' '));
    self::assertTrue($helperFunction('  ', ' '));
    self::assertFalse($helperFunction(' ', '  '));
    self::assertFalse($helperFunction('DAS ist EIN test', 'das IST ein TEST'));
    self::assertFalse($helperFunction('test', null));
    self::assertFalse($helperFunction(null, 'test'));
    self::assertTrue($helperFunction(null, null));
  }
}
