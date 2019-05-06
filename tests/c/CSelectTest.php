<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\{C, Str};
use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\{DataProvider, HackTest}; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

// @oss-disable: <<Oncalls('hack')>>
final class CSelectTest extends HackTest {

  public static function provideTestFindKeyWithKey(
  ): vec<(
    KeyedContainer<string, arraykey>,
    (function(string, arraykey): bool),
    ?string,
  )> {
    return vec[
      tuple(
        darray[],
        ($_, $_) ==> {
          invariant_violation('Don\'t call me! There are no elements!');
          return false;
        },
        null,
      ),
      tuple(
        Map {
          'three_1' => 'the',
          'five_1' => 'quick',
          'five_2' => 'brown',
          'three_2' => 'fox',
        },
        ($length, $word) ==>
          Str\length($word as string) === 5 && $length === 'five_1',
        'five_1',
      ),
      tuple(
        dict[
          'zero' => 0,
          'one' => 1,
          'two' => 2,
        ],
        ($key, $n) ==> $n === 2 && $key === 'two',
        'two',
      ),
    ];
  }

  <<DataProvider('provideTestFindKeyWithKey')>>
  public function testFindKeyWithKey<Tk, Tv>(
    KeyedTraversable<Tk, Tv> $traversable,
    (function(Tk, Tv): bool) $value_predicate,
    ?Tv $expected,
  ): void {
    expect(C\find_key_with_key($traversable, $value_predicate))->toBeSame(
      $expected,
    );
  }

}
