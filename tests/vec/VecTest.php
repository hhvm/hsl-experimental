<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\{Str, Vec};

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\{DataProvider, HackTest};

/** 
 * This is basic coverage of Vec\is_sorted and Vec\is_sorted_by
 */
final class VecTest extends HackTest {

  public function provideStableStrictOrdered(): vec<(Container<num>, bool)> {
    return vec[
      tuple(vec[], true),
      tuple(vec[0], true),
      tuple(vec[0, 1], true),
      tuple(vec[0, 1, 5, 10], true),
      tuple(vec[-5, 0, 5], true),
      tuple(vec[0, 2, 1], false),
      tuple(vec[-5, 0, -5], false),
    ];
  }

  <<DataProvider('provideStableStrictOrdered')>>
  public function testStableStrictOrdering(
    Container<num> $c,
    bool $result,
  ): void {
    expect(Vec\is_sorted($c))->toBeSame($result);
  }

  public function provideStableWeakOrdered(): vec<(Container<num>, bool)> {
    return vec[
      tuple(vec[0, 0], true),
      tuple(vec[0, 1, 1], true),
      tuple(vec[-5, -4, -4, -4, -1, 3, 3, 5], true),
      tuple(vec[-5, -4, -4, -4, -1, 3, 2, 5], false),
    ];
  }

  <<DataProvider('provideStableWeakOrdered')>>
  public function testStableWeakOrdering(
    Container<num> $c,
    bool $result,
  ): void {
    expect(Vec\is_sorted($c))->toBeSame($result);
  }

  public function provideStableStrictOrderedUsingBy(
  ): vec<(Container<string>, (function(string, string): int), bool)> {
    $strlen_shaceship = (string $a, string $b) ==>
      Str\length($a) <=> Str\length($b);

    return vec[
      tuple(vec[], ($_, $_) ==> -1, true),
      tuple(vec[], ($_, $_) ==> 1, true),
      tuple(vec[''], $strlen_shaceship, true),
      tuple(vec['', 'a'], $strlen_shaceship, true),
      tuple(vec['', 'a', 'aaa', 'aaaaa'], $strlen_shaceship, true),
      tuple(vec['', 'a', 'aaaaaa', 'aaaaa'], $strlen_shaceship, false),
    ];
  }

  <<DataProvider('provideStableStrictOrderedUsingBy')>>
  public function testStableStrictOrderingUsingBy<T>(
    Container<T> $c,
    (function(T, T): int) $spaceship_func,
    bool $result,
  ): void {
    expect(Vec\is_sorted_by($c, $spaceship_func))->toBeSame($result);
  }


  public function provideWeakStrictOrderedUsingBy(
  ): vec<(Container<string>, (function(string, string): int), bool)> {
    $strlen_shaceship = (string $a, string $b) ==>
      Str\length($a) <=> Str\length($b);

    return vec[
      tuple(vec['a', 'a'], $strlen_shaceship, true),
      tuple(vec['aa', 'aaa', 'aaa'], $strlen_shaceship, true),
      tuple(vec['a', 'aa', 'aa', 'aa', 'aaa', 'aaa'], $strlen_shaceship, true),
      tuple(vec['aa', 'aa', 'aaaa', 'aaa', 'aaa'], $strlen_shaceship, false),
    ];
  }

  <<DataProvider('provideWeakStrictOrderedUsingBy')>>
  public function testWeakStrictOrderingUsingBy<T>(
    Container<T> $c,
    (function(T, T): int) $spaceship_func,
    bool $result,
  ): void {
    expect(Vec\is_sorted_by($c, $spaceship_func))->toBeSame($result);
  }

}
