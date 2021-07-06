<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\_Private\_DateTime;
use const HH\Lib\Experimental\_Private\_DateTime\NS_IN_SEC;
use type HH\Lib\Experimental\Time;

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\{DataProvider, HackTest};

abstract class TimestampTestBase extends HackTest {

  abstract const type TTimestamp as _DateTime\Timestamp;

  abstract protected static function fromRaw(
    int $seconds,
    int $nanoseconds = 0,
  ): this::TTimestamp;

  abstract protected static function asComparable(
    this::TTimestamp $timestamp,
  ): this::TTimestamp::TComparableTo;

  final public static function provideNormalized(): vec<(int, int, int, int)> {
    return vec[
      // input seconds, input nanoseconds, normalized seconds, normalized ns
      tuple(0, 0, 0, 0),
      tuple(42, 0, 42, 0),
      tuple(0, 42, 0, 42),
      tuple(-42, 0, -42, 0),
      tuple(0, -42, -1, NS_IN_SEC - 42),
      tuple(42, 42, 42, 42),
      tuple(-42, 42, -42, 42),
      tuple(42, -42, 41, NS_IN_SEC - 42),
      tuple(-42, -42, -43, NS_IN_SEC - 42),
      tuple(1, 2 * NS_IN_SEC + 42, 3, 42),
      tuple(-1, 2 * NS_IN_SEC + 42, 1, 42),
      tuple(1, -(2 * NS_IN_SEC) - 42, -2, NS_IN_SEC - 42),
      tuple(1, -(2 * NS_IN_SEC) + 42, -1, 42),
      tuple(-1, -(2 * NS_IN_SEC) - 42, -4, NS_IN_SEC - 42),
      tuple(-1, -(2 * NS_IN_SEC) + 42, -3, 42),
    ];
  }

  <<DataProvider('provideNormalized')>>
  final public function testNormalized(
    int $input_seconds,
    int $input_nanoseconds,
    int $normalized_seconds,
    int $normalized_nanoseconds,
  ): void {
    $ts = static::fromRaw($input_seconds, $input_nanoseconds);
    expect($ts->getSeconds())->toEqual($normalized_seconds);
    expect($ts->getNanoseconds())->toEqual($normalized_nanoseconds);
    expect($ts->toRaw())
      ->toEqual(tuple($normalized_seconds, $normalized_nanoseconds));
  }

  final public static function provideCompare(): vec<(int, int, int, int, int)> {
    return vec[
      // seconds #1, ns #1, seconds #2, ns #2, expected result
      tuple(42, 0, 44, -(2 * NS_IN_SEC), 0),
      tuple(42, 0, 42, -42, 1),
      tuple(0, -1, 0, 1, -1),
      tuple(1, 0, 0, 2 * NS_IN_SEC, -1),
    ];
  }

  <<DataProvider('provideCompare')>>
  final public function testCompare(
    int $s1,
    int $ns1,
    int $s2,
    int $ns2,
    int $expected,
  ): void {
    $a = static::fromRaw($s1, $ns1);
    $a_comp = static::asComparable($a);
    $b = static::fromRaw($s2, $ns2);
    $b_comp = static::asComparable($b);
    expect($a->compare($b_comp))->toEqual($expected);
    expect($b->compare($a_comp))->toEqual(-$expected);
    expect($a->isAtTheSameTime($b_comp))->toEqual($expected === 0);
    expect($a->isBefore($b_comp))->toEqual($expected === -1);
    expect($a->isBeforeOrAtTheSameTime($b_comp))->toEqual($expected !== 1);
    expect($a->isAfter($b_comp))->toEqual($expected === 1);
    expect($a->isAfterOrAtTheSameTime($b_comp))->toEqual($expected !== -1);
    expect($a->isBetweenExclusive($a_comp, $a_comp))->toBeFalse();
    expect($a->isBetweenExclusive($a_comp, $b_comp))->toBeFalse();
    expect($a->isBetweenExclusive($b_comp, $b_comp))->toBeFalse();
    expect($a->isBetweenExclusive($b_comp, $a_comp))->toBeFalse();
    expect($a->isBetweenInclusive($a_comp, $a_comp))->toBeTrue();
    expect($a->isBetweenInclusive($a_comp, $b_comp))->toBeTrue();
    expect($a->isBetweenInclusive($b_comp, $a_comp))->toBeTrue();
    expect($a->isBetweenInclusive($b_comp, $b_comp))->toEqual($expected === 0);
  }

  final public function testIsBetween(): void {
    $a = static::fromRaw(-1, 0);
    $a_comp = static::asComparable($a);
    $b = static::fromRaw(0, 0);
    $b_comp = static::asComparable($b);
    $c = static::fromRaw(0, 1);
    $c_comp = static::asComparable($c);
    expect($b->isBetweenExclusive($a_comp, $c_comp))->toBeTrue();
    expect($b->isBetweenExclusive($c_comp, $a_comp))->toBeTrue();
    expect($b->isBetweenInclusive($a_comp, $c_comp))->toBeTrue();
    expect($b->isBetweenInclusive($c_comp, $a_comp))->toBeTrue();
    expect($a->isBetweenExclusive($b_comp, $c_comp))->toBeFalse();
    expect($a->isBetweenInclusive($c_comp, $b_comp))->toBeFalse();
    expect($c->isBetweenInclusive($a_comp, $b_comp))->toBeFalse();
    expect($c->isBetweenExclusive($b_comp, $a_comp))->toBeFalse();
  }

  final public function testPlusMinus(): void {
    expect(static::fromRaw(123, 456)->plus(Time::hours(2))->toRaw())
      ->toEqual(tuple(123 + 3600 * 2, 456));
    expect(static::fromRaw(123, 456)->plusHours(-2)->toRaw())
      ->toEqual(tuple(123 - 3600 * 2, 456));
    expect(static::fromRaw(123, 456)->plusMinutes(0)->toRaw())
      ->toEqual(tuple(123, 456));
    expect(static::fromRaw(123, 456)->plusSeconds(42)->toRaw())
      ->toEqual(tuple(123 + 42, 456));
    expect(static::fromRaw(123, 456)->plusNanoseconds(NS_IN_SEC + 42)->toRaw())
      ->toEqual(tuple(123 + 1, 456 + 42));
    expect(static::fromRaw(0, 0)->minus(Time::nanoseconds(1))->toRaw())
      ->toEqual(tuple(-1, NS_IN_SEC - 1));
    expect(static::fromRaw(123, 456)->minusHours(-2)->toRaw())
      ->toEqual(tuple(123 + 3600 * 2, 456));
    expect(static::fromRaw(123, 456)->minusMinutes(1)->toRaw())
      ->toEqual(tuple(123 - 60, 456));
    expect(static::fromRaw(123, 456)->minusSeconds(125)->toRaw())
      ->toEqual(tuple(-2, 456));
    expect(static::fromRaw(123, 456)->minusNanoseconds(-NS_IN_SEC)->toRaw())
      ->toEqual(tuple(123 + 1, 456));
  }

  final public static function provideTimeSince(
  ): vec<(int, int, int, int, Time)> {
    return vec[
      // seconds #1, ns #1, seconds #2, ns #2, expected result
      tuple(0, 0, 123, 456, Time::fromParts(0, 0, 123, 456)),
      tuple(-1, 0, 1, 0, Time::seconds(2)),
      tuple(-1, 1, 1, 0, Time::fromParts(0, 0, 1, NS_IN_SEC - 1)),
      tuple(123, 456, 123 + 3600 + 60 + 1, 457, Time::fromParts(1, 1, 1, 1)),
      tuple(-7200, 0, 3600, 42, Time::fromParts(3, 0, 0, 42)),
      tuple(-120, 0, -60, 0, Time::minutes(1)),
      tuple(-1, NS_IN_SEC - 1, 0, 0, Time::nanoseconds(1)),
    ];
  }

  <<DataProvider('provideTimeSince')>>
  final public function testTimeSince(
    int $a_seconds,
    int $a_nanoseconds,
    int $b_seconds,
    int $b_nanoseconds,
    Time $expected,
  ): void {
    $a = static::fromRaw($a_seconds, $a_nanoseconds);
    $a_comp = static::asComparable($a);
    $b = static::fromRaw($b_seconds, $b_nanoseconds);
    $b_comp = static::asComparable($b);
    expect($b->timeSince($a_comp)->isEqual($expected))->toBeTrue();
    expect($a->timeSince($b_comp)->isEqual($expected->invert()))->toBeTrue();
  }
}
