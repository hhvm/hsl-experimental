<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\Str\Grapheme;
use function Facebook\FBExpect\expect;
// @oss-disable: use InvariantViolationException as InvariantException;

final class GraphemeIntrospectTest extends PHPUnit_Framework_TestCase {

  public static function provideLength(): varray<mixed> {
    return varray[
      tuple('', 0),
      tuple('0', 1),
      tuple('hello', 5),
      tuple('مرحبا عالم', 10),
      tuple('héllö wôrld', 11),
      tuple('こんにちは世界', 7),
	  tuple('👨‍👨‍👧‍👧', 1),
	  tuple('각', 1),
    ];
  }

  /** @dataProvider provideLength */
  public function testLength(
    string $string,
    int $expected,
  ): void {
    expect(Grapheme\length($string))->toBeSame($expected);
  }

  public static function provideSearch(): varray<mixed> {
    return varray[
      tuple('', 'foo', 0, null),
      tuple('foöBar', 'öB', 0, 2),
      tuple('foöBar', 'öB', 3, null),
      tuple('foöbar', 'öB', 0, null),
      tuple('foo', 'o', 3, null),
      tuple('héllö wôrld', 'ow', 0, null),
      tuple('héllö wôrld', 'wôrld', -3, null),
	  tuple('🤷‍a👨‍👨‍👧‍👧‍‍‍', '👨‍👨‍👧‍👧‍‍‍', 0, 2),
    ];
  }

  /** @dataProvider provideSearch */
  public function testSearch(
    string $haystack,
    string $needle,
    int $offset,
    ?int $expected,
  ): void {
    expect(Grapheme\search($haystack, $needle, $offset))->toBeSame($expected);
  }

  public static function provideSearchCI(): varray<mixed> {
    return varray[
      tuple('', 'foo', 0, null),
      tuple('foöBar', 'öb', 0, 2),
      tuple('foöBar', 'öb', 3, null),
      tuple('foöbar', 'öB', 0, 2),
      tuple('foo', 'o', 3, null),
      tuple('héllö wôrld', 'ow', 0, null),
      tuple('héllö wôrld', 'Wôrld', -3, null),
      tuple('héllö wôrld', 'WÔRLD', -5, 6),
	  tuple('a👨‍👨‍👧‍👧', '👨‍👨‍👧‍👧‍‍‍', 0, 1),
    ];
  }

  /** @dataProvider provideSearchCI */
  public function testSearchCI(
    string $haystack,
    string $needle,
    int $offset,
    ?int $expected,
  ): void {
    expect(Grapheme\search_ci($haystack, $needle, $offset))->toBeSame($expected);
  }

  public function testPositionExceptions(): void {
    expect(() ==> Grapheme\search('foo', 'f', 5))
      ->toThrow(InvariantException::class);
    expect(() ==> Grapheme\search('héllö wôrld', 'wôrld', -16))
      ->toThrow(InvariantException::class);

    expect(() ==> Grapheme\search_ci('foo', 'f', 5))
      ->toThrow(InvariantException::class);
    expect(() ==> Grapheme\search_ci('héllö wôrld', 'wôrld', -16))
      ->toThrow(InvariantException::class);
  }
}