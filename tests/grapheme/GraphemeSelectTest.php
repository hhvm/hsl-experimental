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

/**
 * @emails oncall+hack
 */
final class GraphemeSelectTest extends PHPUnit_Framework_TestCase {

  public static function provideSlice(): varray<mixed> {
    return varray[
      tuple('héllö wôrld', 3, 3, 'lö '),
      tuple('héllö wôrld', 3, null, 'lö wôrld'),
      tuple('héllö wôrld', 3, 0, ''),
      tuple('fôo', 3, null, ''),
      tuple('fôo', 3, 12, ''),
      tuple('héllö wôrld', -5, null, 'wôrld'),
      tuple('héllö wôrld', -5, 100, 'wôrld'),
      tuple('héllö wôrld', -5, 3, 'wôr'),
	  tuple('a👨‍👨‍👧‍👧 foo', 1, null, '👨‍👨‍👧‍👧 foo'),
    ];
  }

  /** @dataProvider provideSlice */
  public function testSlice(
    string $string,
    int $offset,
    ?int $length,
    string $expected,
  ): void {
    expect(Grapheme\slice($string, $offset, $length))->toBeSame($expected);
  }

  public function testSliceExceptions(): void {
    expect(() ==> Grapheme\slice('héllö', 0, -1))
      ->toThrow(InvariantException::class);
    expect(() ==> Grapheme\slice('héllö', 10))
      ->toThrow(InvariantException::class);
    expect(() ==> Grapheme\slice('héllö', -6))
      ->toThrow(InvariantException::class);
  }

  public static function provideExtract(): varray<mixed> {
    return varray[
      tuple('héllö wôrld', 1, 0, tuple('h', 1)),
	  tuple('héllö wôrld', 1, 1, tuple('é', 3)),
	  tuple('héllö wôrld', 3, 3, tuple('llö', 7)),
    ];
  }

  /** @dataProvider provideExtract */
  public function testExtract(
    string $string,
    int $offset,
    int $next,
    (string, int) $expected,
  ): void {
    expect(Grapheme\extract($string, $offset, $next))->toBeSame($expected);
  }
}
