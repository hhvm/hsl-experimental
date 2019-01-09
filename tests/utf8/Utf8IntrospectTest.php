<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\Str\Utf8;
use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

final class Utf8IntrospectTest extends \Facebook\HackTest\HackTest {

  public static function provideLength(): vec<(string, int)> {
    return vec[
      tuple('', 0),
      tuple('0', 1),
      tuple('hello', 5),
      tuple('مرحبا عالم', 10),
      tuple('héllö wôrld', 11),
      tuple('こんにちは世界', 7),
    ];
  }

  <<DataProvider('provideLength')>>
  public function testLength(string $string, int $expected): void {
    expect(Utf8\length($string))->toBeSame($expected);
  }

  public static function provideSearch(): vec<(string, string, int, ?int)> {
    return vec[
      tuple('', 'foo', 0, null),
      tuple('foöBar', 'öB', 0, 2),
      tuple('foöBar', 'öB', 3, null),
      tuple('foöbar', 'öB', 0, null),
      tuple('foo', 'o', 3, null),
      tuple('héllö wôrld', 'ow', 0, null),
      tuple('héllö wôrld', 'wôrld', -3, null),
      tuple('héllö wôrld', 'wôrld', -5, 6),
    ];
  }

  <<DataProvider('provideSearch')>>
  public function testSearch(
    string $haystack,
    string $needle,
    int $offset,
    ?int $expected,
  ): void {
    expect(Utf8\search($haystack, $needle, $offset))->toBeSame($expected);
  }

  public static function provideSearchCI(): vec<(string, string, int, ?int)> {
    return vec[
      tuple('', 'foo', 0, null),
      tuple('foöBar', 'öb', 0, 2),
      tuple('foöBar', 'öb', 3, null),
      tuple('foöbar', 'öB', 0, 2),
      tuple('foo', 'o', 3, null),
      tuple('héllö wôrld', 'ow', 0, null),
      tuple('héllö wôrld', 'Wôrld', -3, null),
      tuple('héllö wôrld', 'WÔRLD', -5, 6),
    ];
  }

  <<DataProvider('provideSearchCI')>>
  public function testSearchCI(
    string $haystack,
    string $needle,
    int $offset,
    ?int $expected,
  ): void {
    expect(Utf8\search_ci($haystack, $needle, $offset))->toBeSame($expected);
  }

  public static function provideSearchLast(): vec<(string, string, int, ?int)> {
    return vec[
      tuple('foofoofoo', 'foo', 0, 6),
      tuple('foofoofoo', 'bar', 0, null),
      tuple('foobarbar', 'foo', 3, null),
      tuple('foofoofoo', 'Foo', 0, null),
      tuple('foo', 'o', 3, null),
      tuple('foofoofoo', 'foo', -3, 6),
      tuple('foofoofoo', 'foo', -4, 3),
      tuple('héllö wôrld', 'wôrld', -3, 6),
      tuple('héllö wôrld', 'wôrld', -5, 6),
      tuple('héllö wôrld', 'wôrld', -6, null),
    ];
  }

  <<DataProvider('provideSearchLast')>>
  public function testSearchLast(
    string $haystack,
    string $needle,
    int $offset,
    ?int $expected,
  ): void {
    expect(Utf8\search_last($haystack, $needle, $offset))->toBeSame($expected);
  }

  public function testPositionExceptions(): void {
    expect(() ==> Utf8\search('foo', 'f', 5))
      ->toThrow(InvariantException::class);
    expect(() ==> Utf8\search('héllö wôrld', 'wôrld', -16))
      ->toThrow(InvariantException::class);

    expect(() ==> Utf8\search_ci('foo', 'f', 5))
      ->toThrow(InvariantException::class);
    expect(() ==> Utf8\search_ci('héllö wôrld', 'wôrld', -16))
      ->toThrow(InvariantException::class);

    expect(() ==> Utf8\search_last('foo', 'f', 5))
      ->toThrow(InvariantException::class);
    expect(() ==> Utf8\search_last('héllö wôrld', 'wôrld', -16))
      ->toThrow(InvariantException::class);
  }

  public static function provideIsUtf8(): vec<(string, bool)> {
    return vec[
      tuple('', true),
      tuple('foo', true),
      tuple('مرحبا عالم', true),
      tuple('héllö wôrld', true),
      tuple('こんにちは世界', true),
      tuple("h\351ll\366 w\364rld", false),
      tuple("\xc3\x28", false),
      tuple("\xf0\x28\x8c\x28", false),
    ];
  }

  <<DataProvider('provideIsUtf8')>>
  public function testIsUtf8(string $string, bool $expected): void {
    expect(Utf8\is_utf8($string))->toBeSame($expected);
  }

}
