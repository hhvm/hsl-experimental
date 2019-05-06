<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Vec;
use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

final class IterateTest extends \Facebook\HackTest\HackTest {

  public static function generator(): Generator<int, int, void> {
    yield 0 => 5;
    yield 1 => 6;
    yield 2 => 7;
    yield 3 => 8;
    yield 4 => 9;
  }

  public function provideTraversables(): vec<(KeyedTraversable<int, int>)> {
    return vec[
      tuple(Vector {5, 6, 7, 8, 9}),
      tuple(vec[5, 6, 7, 8, 9]),
      tuple(self::generator()),
    ];
  }

  <<DataProvider('provideTraversables')>>
  public function testAppliesAFunctionToEachElement(
    Traversable<int> $traversable,
  ): void {
    $throwOnEight = $value ==> {
      if ($value === 8) {
        throw new InvalidArgumentException('Eight found!');
      }
    };
    expect(() ==> Vec\for_each($traversable, $throwOnEight))->toThrow(
      InvalidArgumentException::class,
      'Eight found!',
      'Function not called on the element 8!',
    );
  }

  <<DataProvider('provideTraversables')>>
  public function testAppliesAFunctionToEachElementAndKey(
    KeyedTraversable<int, int> $traversable,
  ): void {
    $throwOnThreeAndEight = ($key, $value) ==> {
      if ($key === 3 && $value === 8) {
        throw new InvalidArgumentException('Eight and three found!');
      }
    };
    expect(() ==> Vec\for_each_with_key($traversable, $throwOnThreeAndEight))
      ->toThrow(
        InvalidArgumentException::class,
        'Eight and three found!',
        'Function not called on the key 3 and the value 8!',
      );
  }

}
