<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Dict;
use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

final class DictIterateTest extends \Facebook\HackTest\HackTest {

  public static function generator(): Generator<string, int, void> {
    yield 'five' => 5;
    yield 'six' => 6;
    yield 'seven' => 7;
    yield 'eight' => 8;
    yield 'nine' => 9;
  }

  public function provideTraversables(): vec<(KeyedTraversable<string, int>)> {
    return vec[
      tuple(
        Map {'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9},
      ),
      tuple(
        dict['five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9],
      ),
      tuple(self::generator()),
    ];
  }

  <<DataProvider('provideTraversables')>>
  public function testAppliesAFunctionToEachElement(
    KeyedTraversable<string, int> $traversable,
  ): void {
    $throwOnEight = $value ==> {
      if ($value === 8) {
        throw new InvalidArgumentException('Eight found!');
      }
    };
    expect(() ==> Dict\for_each($traversable, $throwOnEight))->toThrow(
      InvalidArgumentException::class,
      'Eight found!',
      'Function not called on the element 8!',
    );
  }

  <<DataProvider('provideTraversables')>>
  public function testAppliesAFunctionToEachElementAndKey(
    KeyedTraversable<string, int> $traversable,
  ): void {
    $throwOnThreeAndEight = ($key, $value) ==> {
      if ($key === 'eight' && $value === 8) {
        throw new InvalidArgumentException('Eight and eight found!');
      }
    };
    expect(() ==> Dict\for_each_with_key($traversable, $throwOnThreeAndEight))
      ->toThrow(
        InvalidArgumentException::class,
        'Eight and eight found!',
        'Function not called on the key 3 and the value 8!',
      );
  }

  <<DataProvider('provideTraversables')>>
  public function testForeachReturnsValuesFromTraversable(
    KeyedTraversable<int, int> $traversable,
  ): void {
    expect(Dict\for_each($traversable, $_ ==> {}))->toBeSame(
      dict['five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9],
      'Values are not being returned',
    );
  }

  <<DataProvider('provideTraversables')>>
  public function testForeachWithKeyReturnsValuesFromTraversable(
    KeyedTraversable<int, int> $traversable,
  ): void {
    expect(Dict\for_each_with_key($traversable, ($_, $_) ==> {}))->toBeSame(
      dict['five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9],
      'Values are not being returned',
    );
  }

}
