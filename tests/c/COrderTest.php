<?hh // strict

/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\{C, Str, Vec};
use function Facebook\FBExpect\expect;
use type Facebook\HackTest\{DataProvider, HackTest};

// @oss-disable: <<Oncalls('hack')>>
final class COrderTest extends HackTest {

  public function provideSortableVecs(): vec<(vec<mixed>, bool)> {
    return vec[
      // Empty containers are sorted
      tuple(vec[], true),
      // Containers with one element are sorted
      tuple(vec[0], true),
      // Containers with two ascending elements are sorted
      tuple(vec[0, 1], true),
      // Containers with two descending elements are not sorted
      tuple(vec[1, 0], false),
      // Containers with two strictly equal elements are sorted
      tuple(vec[100, 100], true),
      // Containers with two weakly equal elements are sorted
      tuple(vec[100., 100], true),
      // Containers with n sorted elements are sorted
      tuple(Vec\range(100, 1000, 11), true),
      // Containers with n reversed elements are not sorted
      tuple(Vec\range(1000, 100, 11), false),
      // Containers with alphabetically sorted strings are sorted 
      tuple(vec['aaa', 'bbb', 'c', 'ca', 'ccc'], true),
      // Containers with lexiographically sorted strings are not sorted 
      tuple(vec['a', 'b', 'aa', 'bb', 'aaa', 'bbb'], false),
    ];
  }

  <<DataProvider('provideSortableVecs')>>
  public function testSortingWithoutComparator(
    Traversable<mixed> $t,
    bool $expect,
  ): void {
    expect(C\is_sorted($t))->toBeSame($expect);
  }

  public function provideSortableTraversables(
  ): vec<(vec<Traversable<mixed>>, bool)> {
    return Vec\map(
      $this->provideSortableVecs(),
      $tuple ==> tuple(self::vecToAllTraversableTypes($tuple[0]), $tuple[1]),
    );
  }

  <<DataProvider('provideSortableTraversables')>>
  public function testSortingTraversablesWithoutComparator(
    vec<Traversable<mixed>> $ts,
    bool $expect,
  ): void {
    foreach ($ts as $t) {
      expect(C\is_sorted($t))->toBeSame(
        $expect,
        'Sorting failed for a Traversable of type %s',
        is_object($t) ? get_class($t) : gettype($t),
      );
    }
  }

  public function provideNonSortableVecs(
  ): vec<(vec<string>, (function(string, string): int), bool)> {
    $str_len_cmp = (string $a, string $b) ==> Str\length($a) <=> Str\length($b);
    return vec[
      tuple(vec['short', 'longer', 'longest'], $str_len_cmp, true),
      tuple(vec['short', 'tiny', 'longest'], $str_len_cmp, false),
    ];
  }

  <<DataProvider('provideNonSortableVecs')>>
  public function testSortingWithComparator<Tv>(
    Traversable<Tv> $t,
    (function(Tv, Tv): int) $comparator,
    bool $expect,
  ): void {
    expect(C\is_sorted($t, $comparator))->toBeSame($expect);
  }

  public function provideNonSortableTraversables(
  ): vec<(vec<Traversable<string>>, (function(string, string): int), bool)> {
    return Vec\map(
      $this->provideNonSortableVecs(),
      $tuple ==>
        tuple(self::vecToAllTraversableTypes($tuple[0]), $tuple[1], $tuple[2]),
    );
  }

  <<DataProvider('provideNonSortableTraversables')>>
  public function testSortingTraversablesWithComparator(
    vec<Traversable<string>> $ts,
    (function(string, string): int) $cmp,
    bool $expect,
  ): void {
    foreach ($ts as $t) {
      expect(C\is_sorted($t, $cmp))->toBeSame(
        $expect,
        'Sorting failed for a Traversable of type %s',
        is_object($t) ? get_class($t) : gettype($t),
      );
    }
  }

  private static function vecToAllTraversableTypes<Tv>(
    vec<Tv> $vec,
  ): vec<Traversable<Tv>> {
    $traversable_to_generator = $traversable ==> {
      foreach ($traversable as $value) {
        yield $value;
      }
    };

    return vec[
      $vec,
      $traversable_to_generator($vec),
      varray($vec),
      darray($vec),
      dict($vec),
      new Vector($vec),
      new ImmVector($vec),
      new Map($vec),
      new ImmMap($vec),
    ];
  }
}
