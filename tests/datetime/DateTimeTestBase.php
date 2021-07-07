<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\DateTime;

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\{DataProvider, HackTest};

abstract class DateTimeTestBase extends HackTest {

  abstract const type TDateTime as DateTime\DateTime;

  abstract protected static function fromParts(
    int $years,
    int $months,
    int $days,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): DateTime\Builder<this::TDateTime>;

  abstract protected static function asComparable(
    this::TDateTime $dt,
  ): this::TDateTime::TComparableTo;

  final public function testGetters(): void {
    $dt = static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX();
    expect($dt->getYear())->toEqual(2021);
    expect($dt->getMonth())->toEqual(2);
    expect($dt->getDay())->toEqual(3);
    expect($dt->getHours())->toEqual(4);
    expect($dt->getMinutes())->toEqual(5);
    expect($dt->getSeconds())->toEqual(6);
    expect($dt->getNanoseconds())->toEqual(7);
    expect($dt->getDate())->toEqual(tuple(2021, 2, 3));
    expect($dt->getTime())->toEqual(tuple(4, 5, 6, 7));
    expect($dt->getParts())->toEqual(tuple(2021, 2, 3, 4, 5, 6, 7));
  }

  final public function testSetters(): void {
    // Setters should work on DateTime instances as well as DateTimeBuilders,
    // and always return a DateTimeBuilder.
    $builder = static::fromParts(2021, 2, 3, 4, 5, 6, 7);
    $instance = $builder->exactX();

    foreach (vec[$builder, $instance] as $target) {
      expect($target->withYear(1999)->exactX()->getParts())
        ->toEqual(tuple(1999, 2, 3, 4, 5, 6, 7));
      expect($target->withMonth(8)->exactX()->getParts())
        ->toEqual(tuple(2021, 8, 3, 4, 5, 6, 7));
      expect($target->withDay(9)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 9, 4, 5, 6, 7));
      expect($target->withHours(10)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 10, 5, 6, 7));
      expect($target->withMinutes(11)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 4, 11, 6, 7));
      expect($target->withSeconds(12)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 4, 5, 12, 7));
      expect($target->withNanoseconds(13)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 4, 5, 6, 13));

      // multi-setters
      expect($target->withDate(1984, 1, 14)->exactX()->getParts())
        ->toEqual(tuple(1984, 1, 14, 4, 5, 6, 7));
      // withTime() has 2 optional arguments, so test all 3 possible cases
      expect($target->withTime(15, 16)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 15, 16, 0, 0));
      expect($target->withTime(17, 18, 19)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 17, 18, 19, 0));
      expect($target->withTime(20, 21, 22, 23)->exactX()->getParts())
        ->toEqual(tuple(2021, 2, 3, 20, 21, 22, 23));

      // a chain of setters that also goes through an invalid intermediate state
      expect(
        $target->withTime(16, 42)
          ->withDay(31)
          ->withMonth(5)
          ->exactX()
          ->getParts(),
      )->toEqual(tuple(2021, 5, 31, 16, 42, 0, 0));
    }
  }

  final public static function provideOutOfRange(
  ): vec<(DateTime\Builder<this::TDateTime>, this::TDateTime)> {
    return vec[
      tuple(
        static::fromParts(2021, 0, 3, 4, 5, 6, 7),
        static::fromParts(2021, 1, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 13, 3, 4, 5, 6, 7),
        static::fromParts(2021, 12, 3, 4, 5, 6, 7)->exactX(),
      ),
      // TODO
    ];
  }

  <<DataProvider('provideOutOfRange')>>
  final public function testOutOfRange(
    DateTime\Builder<this::TDateTime> $invalid,
    this::TDateTime $expected_closest,
  ): void {
    expect($invalid->isValid())->toBeFalse();
    expect(() ==> $invalid->exactX())->toThrow(Exception::class);
    expect($invalid->closest()->getParts())
      ->toEqual($expected_closest->getParts());
  }
}
