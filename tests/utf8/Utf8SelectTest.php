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
use function Facebook\FBExpect\expect;
// @oss-disable: use InvariantViolationException as InvariantException;

/**
 * @emails oncall+hack
 */
final class Utf8SelectTest extends \Facebook\HackTest\HackTestCase {

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
    ];
  }

  /** @dataProvider provideSlice */
  public function testSlice(
    string $string,
    int $offset,
    ?int $length,
    string $expected,
  ): void {
    expect(Utf8\slice($string, $offset, $length))->toBeSame($expected);
  }

  public function testSliceExceptions(): void {
    expect(() ==> Utf8\slice('héllö', 0, -1))
      ->toThrow(InvariantException::class);
    expect(() ==> Utf8\slice('héllö', 10))
      ->toThrow(InvariantException::class);
    expect(() ==> Utf8\slice('héllö', -6))
      ->toThrow(InvariantException::class);
  }

  public static function provideSliceBytes(): varray<mixed> {
    return varray[
      tuple('héllö wôrld', 3, 3, 'll'),
      tuple('héllö wôrld', 3, null, 'llö wôrld'),
      tuple('héllö wôrld', 3, 0, ''),
      tuple('fôo', 4, null, ''),
      tuple('fôo', 4, 12, ''),
      tuple('héllö wôrld', -5, null, 'ôrld'),
      tuple('héllö wôrld', -5, 100, 'ôrld'),
      tuple('héllö wôrld', -5, 3, 'ôr'),
    ];
  }

  /** @dataProvider provideSliceBytes */
  public function testSliceBytes(
    string $string,
    int $offset,
    ?int $length,
    string $expected,
  ): void {
    expect(Utf8\slice_bytes($string, $offset, $length))->toBeSame($expected);
  }
}

