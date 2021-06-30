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

use type HH\Lib\Experimental\Time;
use const HH\Lib\Experimental\_Private\_DateTime\NS_IN_SEC;

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\{DataProvider, HackTest}; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hhvm_oss')>>
final class TimeTest extends HackTest {

  public function testGetters(): void {
    $t = Time::fromParts(1, 2, 3, 4);
    expect($t->getHours())->toEqual(1);
    expect($t->getMinutes())->toEqual(2);
    expect($t->getSeconds())->toEqual(3);
    expect($t->getNanoseconds())->toEqual(4);
    expect($t->getParts())->toEqual(tuple(1, 2, 3, 4));
  }

  public function testSetters(): void {
    $t = Time::fromParts(1, 2, 3, 4);
    expect($t->withHours(42)->getParts())->toEqual(tuple(42, 2, 3, 4));
    expect($t->withMinutes(42)->getParts())->toEqual(tuple(1, 42, 3, 4));
    expect($t->withSeconds(42)->getParts())->toEqual(tuple(1, 2, 42, 4));
    expect($t->withNanoseconds(42)->getParts())->toEqual(tuple(1, 2, 3, 42));
    // Verify the result is normalized.
    expect($t->withMinutes(63)->getParts())->toEqual(tuple(2, 3, 3, 4));
    expect($t->withSeconds(63)->getParts())->toEqual(tuple(1, 3, 3, 4));
    expect($t->withNanoseconds(NS_IN_SEC + 42)->getParts())
      ->toEqual(tuple(1, 2, 4, 42));
    // Verify $t hasn't changed (it is immutable).
    expect($t->getParts())->toEqual(tuple(1, 2, 3, 4));
  }

  public function testFractionsOfSecond(): void {
    expect(Time::zero()->getParts())
      ->toEqual(tuple(0, 0, 0, 0));
    expect(Time::nanoseconds(42)->getParts())
      ->toEqual(tuple(0, 0, 0, 42));
    expect(Time::nanoseconds(NS_IN_SEC + 42)->getParts())
      ->toEqual(tuple(0, 0, 1, 42));
    expect(Time::microseconds(42)->getParts())
      ->toEqual(tuple(0, 0, 0, 42000));
    expect(Time::microseconds(1000042)->getParts())
      ->toEqual(tuple(0, 0, 1, 42000));
    expect(Time::milliseconds(42)->getParts())
      ->toEqual(tuple(0, 0, 0, 42000000));
    expect(Time::milliseconds(1042)->getParts())
      ->toEqual(tuple(0, 0, 1, 42000000));
  }

  public static function provideNormalized(): vec<(int, int, int, int)> {
    return vec[
      // input seconds, input ns, normalized seconds, normalized ns
      tuple(0, 0, 0, 0),
      tuple(0, 3, 0, 3),
      tuple(3, 0, 3, 0),
      tuple(1, 3, 1, 3),
      tuple(1, -3, 0, NS_IN_SEC - 3),
      tuple(-1, 3, 0, -(NS_IN_SEC - 3)),
      tuple(-1, -3, -1, -3),
      tuple(1, NS_IN_SEC + 42, 2, 42),
      tuple(1, -(NS_IN_SEC + 42), 0, -42),
      tuple(2, -3, 1, NS_IN_SEC - 3),
    ];
  }

  <<DataProvider('provideNormalized')>>
  public function testNormalized(
    int $input_s,
    int $input_ns,
    int $normalized_s,
    int $normalized_ns,
  ): void {
    expect(Time::fromParts(0, 0, $input_s, $input_ns)->getParts())
      ->toEqual(tuple(0, 0, $normalized_s, $normalized_ns));
  }

  public function testNormalizedHMS(): void {
    expect(Time::fromParts(2, 63, 124)->getParts())
      ->toEqual(tuple(3, 5, 4, 0));
    expect(Time::fromParts(2, -63, 124)->getParts())
      ->toEqual(tuple(0, 59, 4, 0));
    expect(Time::fromParts(0, -63, 124, 42)->getParts())
      ->toEqual(tuple(-1, 0, -55, -(NS_IN_SEC - 42)));

    expect(Time::hours(42)->getParts())
      ->toEqual(tuple(42, 0, 0, 0));
    expect(Time::minutes(63)->getParts())
      ->toEqual(tuple(1, 3, 0, 0));
    expect(Time::seconds(-63)->getParts())
      ->toEqual(tuple(0, -1, -3, 0));
    expect(Time::nanoseconds(-NS_IN_SEC)->getParts())
      ->toEqual(tuple(0, 0, -1, 0));
  }

  public static function providePositiveNegative(
  ): vec<(int, int, int, int, int)> {
    return vec[
      // h, m, s, ns, expected sign
      tuple(0, 0, 0, 0, 0),
      tuple(0, 42, 0, 0, 1),
      tuple(0, 0, -42, 0, -1),
      tuple(1, -63, 0, 0, -1),
    ];
  }

  <<DataProvider('providePositiveNegative')>>
  public function testPositiveNegative(
    int $h,
    int $m,
    int $s,
    int $ns,
    int $expected_sign,
  ): void {
    $t = Time::fromParts($h, $m, $s, $ns);
    expect($t->isZero())->toEqual($expected_sign === 0);
    expect($t->isPositive())->toEqual($expected_sign === 1);
    expect($t->isNegative())->toEqual($expected_sign === -1);
  }

  public static function provideCompare(): vec<(Time, Time, int)> {
    return vec[
      tuple(Time::hours(1), Time::minutes(42), 1),
      tuple(Time::minutes(2), Time::seconds(120), 0),
      tuple(Time::zero(), Time::nanoseconds(1), -1),
    ];
  }

  <<DataProvider('provideCompare')>>
  public function testCompare(Time $a, Time $b, int $expected): void {
    expect($a->compare($b))->toEqual($expected);
    expect($b->compare($a))->toEqual(-$expected);
    expect($a->isEqual($b))->toEqual($expected === 0);
    expect($a->isShorter($b))->toEqual($expected === -1);
    expect($a->isShorterOrEqual($b))->toEqual($expected !== 1);
    expect($a->isLonger($b))->toEqual($expected === 1);
    expect($a->isLongerOrEqual($b))->toEqual($expected !== -1);
    expect($a->isBetweenExcl($a, $a))->toBeFalse();
    expect($a->isBetweenExcl($a, $b))->toBeFalse();
    expect($a->isBetweenExcl($b, $a))->toBeFalse();
    expect($a->isBetweenExcl($b, $b))->toBeFalse();
    expect($a->isBetweenIncl($a, $a))->toBeTrue();
    expect($a->isBetweenIncl($a, $b))->toBeTrue();
    expect($a->isBetweenIncl($b, $a))->toBeTrue();
    expect($a->isBetweenIncl($b, $b))->toEqual($expected === 0);
  }

  public function testIsBetween(): void {
    $a = Time::hours(1);
    $b = Time::minutes(64);
    $c = Time::fromParts(1, 30);
    expect($b->isBetweenExcl($a, $c))->toBeTrue();
    expect($b->isBetweenExcl($c, $a))->toBeTrue();
    expect($b->isBetweenIncl($a, $c))->toBeTrue();
    expect($b->isBetweenIncl($c, $a))->toBeTrue();
    expect($a->isBetweenExcl($b, $c))->toBeFalse();
    expect($a->isBetweenIncl($c, $b))->toBeFalse();
    expect($c->isBetweenIncl($a, $b))->toBeFalse();
    expect($c->isBetweenExcl($b, $a))->toBeFalse();
  }

  public function testOperations(): void {
    $z = Time::zero();
    $a = Time::fromParts(0, 2, 25);
    $b = Time::fromParts(0, 0, -63, 42);
    expect($z->invert()->getParts())->toEqual(tuple(0, 0, 0, 0));
    expect($a->invert()->getParts())->toEqual(tuple(0, -2, -25, 0));
    expect($b->invert()->getParts())->toEqual(tuple(0, 1, 2, NS_IN_SEC - 42));
    expect($z->plus($a)->getParts())->toEqual($a->getParts());
    expect($b->plus($z)->getParts())->toEqual($b->getParts());
    expect($z->minus($b)->getParts())->toEqual($b->getParts());
    expect($a->minus($z)->getParts())->toEqual($a->getParts());
    expect($a->plus($b)->getParts())->toEqual(tuple(0, 1, 22, 42));
    expect($b->plus($a)->getParts())->toEqual(tuple(0, 1, 22, 42));
    expect($a->minus($b)->getParts())
      ->toEqual(tuple(0, 3, 27, NS_IN_SEC - 42));
    expect($b->minus($a)->getParts())
      ->toEqual(tuple(0, -3, -27, -(NS_IN_SEC - 42)));
    expect($a->minus($b)->getParts())
      ->toEqual($b->invert()->plus($a)->getParts());
  }

  public static function provideToString(): vec<(int, int, int, int, string)> {
    return vec[
      // h, m, s, ns, expected output
      tuple(42, 0, 0, 0, '42hr'),
      tuple(0, 42, 0, 0, '42min'),
      tuple(0, 0, 42, 0, '42sec'),
      tuple(0, 0, 0, 0, '0sec'),
      tuple(0, 0, 0, 42, '0sec'), // rounded because default $max_decimals = 3
      tuple(0, 0, 1, 42, '1sec'),
      tuple(0, 0, 1, 20000000, '1.02sec'),
      tuple(1, 2, 0, 0, '1hr 2min'),
      tuple(1, 0, 3, 0, '1hr 0min 3sec'),
      tuple(0, 2, 3, 0, '2min 3sec'),
      tuple(1, 2, 3, 0, '1hr 2min 3sec'),
      tuple(1, 0, 0, 42000000, '1hr 0min 0.042sec'),
      tuple(-42, 0, -42, 0, '-42hr 0min -42sec'),
      tuple(-42, 0, -42, -420000000, '-42hr 0min -42.42sec'),
      tuple(0, 0, 0, -420000000, '-0.42sec'),
    ];
  }

  <<DataProvider('provideToString')>>
  public function testToString(
    int $h,
    int $m,
    int $s,
    int $ns,
    string $expected,
  ): void {
    expect(Time::fromParts($h, $m, $s, $ns)->__toString())->toEqual($expected);
  }
}
