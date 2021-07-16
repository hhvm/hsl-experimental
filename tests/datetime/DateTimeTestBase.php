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

use namespace HH\Lib\Vec;
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

    expect($dt->getYearShort())->toEqual(21);
    expect($dt->withYear(2000)->exactX()->getYearShort())->toEqual(0);
    expect($dt->withYear(1999)->exactX()->getYearShort())->toEqual(99);
    expect($dt->withYear(1904)->exactX()->getYearShort())->toEqual(4);
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

    // Verify original instance is unchanged (objects are immutable).
    expect($instance->getParts())->toEqual(tuple(2021, 2, 3, 4, 5, 6, 7));
  }

  final public static function provideOutOfRange(
  ): vec<(DateTime\Builder<this::TDateTime>, this::TDateTime)> {
    return vec[
      // month
      tuple(
        static::fromParts(2021, 0, 3, 4, 5, 6, 7),
        static::fromParts(2021, 1, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 13, 3, 4, 5, 6, 7),
        static::fromParts(2021, 12, 3, 4, 5, 6, 7)->exactX(),
      ),
      // day
      tuple(
        static::fromParts(2021, 2, -1, 4, 5, 6, 7),
        static::fromParts(2021, 2, 1, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 29, 4, 5, 6, 7),
        static::fromParts(2021, 2, 28, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2020, 2, 42, 4, 5, 6, 7),
        static::fromParts(2020, 2, 29, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(1984, 4, 31, 4, 5, 6, 7),
        static::fromParts(1984, 4, 30, 4, 5, 6, 7)->exactX(),
      ),
      // hours
      tuple(
        static::fromParts(2021, 2, 3, -42, 5, 6, 7),
        static::fromParts(2021, 2, 3, 0, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 42, 5, 6, 7),
        static::fromParts(2021, 2, 3, 23, 5, 6, 7)->exactX(),
      ),
      // minutes
      tuple(
        static::fromParts(2021, 2, 3, 4, -1, 6, 7),
        static::fromParts(2021, 2, 3, 4, 0, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 60, 6, 7),
        static::fromParts(2021, 2, 3, 4, 59, 6, 7)->exactX(),
      ),
      // seconds
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, -1, 7),
        static::fromParts(2021, 2, 3, 4, 5, 0, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 61, 7),
        static::fromParts(2021, 2, 3, 4, 5, 59, 7)->exactX(),
      ),
      // nanoseconds
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, -1),
        static::fromParts(2021, 2, 3, 4, 5, 6, 0)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, NS_IN_SEC),
        static::fromParts(2021, 2, 3, 4, 5, 6, NS_IN_SEC - 1)->exactX(),
      ),
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

  final public static function provideCentury(): vec<(int, int)> {
    return vec[
      // year, century
      tuple(2001, 21),
      tuple(2000, 21),
      tuple(1999, 20),
      tuple(1984, 20),
      tuple(1900, 20),
      tuple(1899, 19),
      tuple(1234, 13),
      tuple(567, 6),
    ];
  }

  <<DataProvider('provideCentury')>>
  final public function testCentury(
    int $year,
    int $expected_century,
  ): void {
    expect(static::fromParts($year, 2, 3, 4, 5, 6, 7)->exactX()->getCentury())
      ->toEqual($expected_century);
  }

  final public static function provideDaysInMonth(): vec<(int, int, int)> {
    return vec[
      // year, month, expected days in month
      tuple(1984, 1, 31),
      tuple(2021, 2, 28),
      tuple(2020, 2, 29),
      tuple(2000, 2, 29),
      tuple(1900, 2, 28),
      tuple(1919, 3, 31),
      tuple(1881, 4, 30),
      tuple(2020, 5, 31),
      tuple(1900, 6, 30),
      tuple(2000, 7, 31),
      tuple(2021, 8, 31),
      tuple(1999, 9, 30),
      tuple(1989, 10, 31),
      tuple(1964, 11, 30),
      tuple(2121, 12, 31),
    ];
  }

  <<DataProvider('provideDaysInMonth')>>
  final public function testDaysInMonth(
    int $year,
    int $month,
    int $expected,
  ): void {
    $dt = static::fromParts($year, $month, 3, 4, 5, 6, 7)->exactX();
    expect($dt->getDaysInMonth())->toEqual($expected);
    if ($month === 2) {
      expect($dt->isLeapYear())->toEqual($expected === 29);
    }
  }

  final public static function provideHoursAmPm(
  ): vec<(int, int, DateTime\AmPm)> {
    return vec[
      // 24-hour value, expected 12-hour value
      tuple(0, 12, DateTime\AmPm::AM),
      tuple(1, 1, DateTime\AmPm::AM),
      tuple(11, 11, DateTime\AmPm::AM),
      tuple(12, 12, DateTime\AmPm::PM),
      tuple(13, 1, DateTime\AmPm::PM),
      tuple(16, 4, DateTime\AmPm::PM),
      tuple(23, 11, DateTime\AmPm::PM),
    ];
  }

  <<DataProvider('provideHoursAmPm')>>
  final public function testHoursAmPm(
    int $hours_24,
    int $expected_hours_12,
    DateTime\AmPm $expected_ampm,
  ): void {
    expect(
      static::fromParts(2021, 2, 3, $hours_24, 5, 6, 7)->exactX()
        ->getHoursAmPm(),
    )->toEqual(tuple($expected_hours_12, $expected_ampm));
  }

  final public static function provideWeekNumber(
  ): vec<(int, int, int, int, int)> {
    return vec[
      // year, month, day, expected (year, week number)
      tuple(1984, 2, 8, 1984, 6),
      tuple(2014, 12, 29, 2015, 1),
      tuple(2014, 12, 31, 2015, 1),
      tuple(2020, 1, 1, 2020, 1),
      tuple(2020, 12, 31, 2020, 53),
      tuple(2021, 1, 1, 2020, 53),
      tuple(2021, 1, 4, 2021, 1),
      tuple(2021, 12, 31, 2021, 52),
    ];
  }

  <<DataProvider('provideWeekNumber')>>
  final public function testWeekNumber(
    int $year,
    int $month,
    int $day,
    int $expected_week_year,
    int $expected_week_number,
  ): void {
    expect(
      static::fromParts($year, $month, $day, 4, 5, 6, 7)->exactX()
        ->getISOWeekNumber(),
    )->toEqual(tuple($expected_week_year, $expected_week_number));
  }

  final public static function provideWeekday(
  ): vec<(int, int, int, DateTime\Weekday, int)> {
    return vec[
      // year, month, day, expected weekday, expected int value (ISO-8601)
      tuple(2021, 7, 5, DateTime\Weekday::MONDAY, 1),
      tuple(2021, 7, 6, DateTime\Weekday::TUESDAY, 2),
      tuple(2021, 7, 7, DateTime\Weekday::WEDNESDAY, 3),
      tuple(2021, 7, 8, DateTime\Weekday::THURSDAY, 4),
      tuple(2021, 7, 9, DateTime\Weekday::FRIDAY, 5),
      tuple(2021, 7, 10, DateTime\Weekday::SATURDAY, 6),
      tuple(2021, 7, 11, DateTime\Weekday::SUNDAY, 7),
    ];
  }

  <<DataProvider('provideWeekday')>>
  final public function testWeekday(
    int $year,
    int $month,
    int $day,
    DateTime\Weekday $expected_weekday,
    int $expected_int_value,
  ): void {
    $weekday = static::fromParts($year, $month, $day, 4, 5, 6, 7)->exactX()
      ->getWeekday();
    expect($weekday)->toEqual($expected_weekday);
    expect($weekday)->toEqual($expected_int_value);
  }

  final public static function provideCompare(
  ): vec<(this::TDateTime, this::TDateTime, int)> {
    return vec[
      tuple(
        static::fromParts(2020, 12, 31, 23, 59, 59, NS_IN_SEC - 1)->exactX(),
        static::fromParts(2021, 1, 1, 0, 0, 0, 0)->exactX(),
        -1,
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        0,
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 8)->exactX(),
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        1,
      ),
      tuple(
        static::fromParts(1234, 2, 3, 4, 5, 6, 8)->exactX(),
        static::fromParts(2345, 2, 3, 4, 5, 6, 7)->exactX(),
        -1,
      ),
    ] |> Vec\concat($$, static::provideCompareSubclass());
  }

  protected static function provideCompareSubclass(
  ): vec<(this::TDateTime, this::TDateTime, int)> {
    return vec[];
  }

  <<DataProvider('provideCompare')>>
  final public function testCompare(
    this::TDateTime $a,
    this::TDateTime $b,
    int $expected,
  ): void {
    $a_comp = static::asComparable($a);
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
    $a = static::fromParts(1900, 2, 3, 4, 5, 6, 7)->exactX();
    $a_comp = static::asComparable($a);
    $b = static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX();
    $b_comp = static::asComparable($b);
    $c = static::fromParts(2021, 2, 3, 4, 5, 6, 8)->exactX();
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

  final public static function provideTimeSince(
  ): vec<(this::TDateTime, this::TDateTime, Time)> {
    return vec[
      tuple(
        static::fromParts(2020, 12, 31, 23, 59, 59, NS_IN_SEC - 1)->exactX(),
        static::fromParts(2021, 1, 1, 0, 0, 0, 0)->exactX(),
        Time::nanoseconds(1),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        Time::zero(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 8)->exactX(),
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        Time::nanoseconds(-1),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        static::fromParts(2021, 3, 4, 5, 7, 9, 11)->exactX(),
        Time::fromParts(29 * 24 + 1, 2, 3, 4),
      ),
    ];
  }

  <<DataProvider('provideTimeSince')>>
  final public function testTimeSince(
    this::TDateTime $a,
    this::TDateTime $b,
    Time $expected,
  ): void {
    $a_comp = static::asComparable($a);
    $b_comp = static::asComparable($b);
    expect($b->timeSince($a_comp)->isEqual($expected))->toBeTrue();
    expect($a->timeSince($b_comp)->isEqual($expected->invert()))->toBeTrue();
  }

  final public function testPlusMinusTime(): void {
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->plus(Time::hours(2))
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 6, 5, 6, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->plusHours(-2)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 2, 5, 6, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->plusMinutes(0)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 5, 6, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->plusSeconds(42)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 5, 48, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->plusNanoseconds(NS_IN_SEC + 42)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 5, 7, 49));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->minus(Time::nanoseconds(1))
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 5, 6, 6));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->minusHours(-2)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 6, 5, 6, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->minusMinutes(1)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 4, 6, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->minusSeconds(125)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 3, 1, 7));
    expect(
      static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX()
        ->minusNanoseconds(-NS_IN_SEC)
        ->getParts(),
    )->toEqual(tuple(2021, 2, 3, 4, 5, 7, 7));
  }

  final public static function provideValidPlusMinusDate(): vec<(
    this::TDateTime,
    (function(this::TDateTime): DateTime\Builder<this::TDateTime>),
    this::TDateTime,
  )> {
    return vec[
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->plusYears(2),
        static::fromParts(2023, 2, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->plusMonths(-3),
        static::fromParts(2020, 11, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->plusDays(0),
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->minusYears(-100),
        static::fromParts(2121, 2, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->minusMonths(25),
        static::fromParts(2019, 1, 3, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 2, 3, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->minusDays(366),
        static::fromParts(2020, 2, 3, 4, 5, 6, 7)->exactX(),
      ),
    ];
  }

  <<DataProvider('provideValidPlusMinusDate')>>
  final public function testValidPlusMinusDate(
    this::TDateTime $original,
    (function(this::TDateTime): DateTime\Builder<this::TDateTime>) $operation,
    this::TDateTime $expected,
  ): void {
    $builder = $operation($original);
    expect($builder->isValid())->toBeTrue();
    expect($builder->exactX()->isAtTheSameTime(static::asComparable($expected)))
      ->toBeTrue();
    expect($builder->exactX()->getParts())->toEqual($expected->getParts());
    expect($builder->closest()->getParts())->toEqual($expected->getParts());
  }

  final public static function provideInvalidPlusMinusDate(): vec<(
    this::TDateTime,
    (function(this::TDateTime): DateTime\Builder<this::TDateTime>),
    this::TDateTime,
  )> {
    return vec[
      tuple(
        static::fromParts(2020, 2, 29, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->plusYears(1),
        static::fromParts(2021, 2, 28, 4, 5, 6, 7)->exactX(),
      ),
      tuple(
        static::fromParts(2021, 1, 31, 4, 5, 6, 7)->exactX(),
        $dt ==> $dt->minusMonths(2),
        static::fromParts(2020, 11, 30, 4, 5, 6, 7)->exactX(),
      ),
      // plus/minusDays can only be invalid on Zoned due to DST changes
    ];
  }

  <<DataProvider('provideInvalidPlusMinusDate')>>
  final public function testInvalidPlusMinusDate(
    this::TDateTime $original,
    (function(this::TDateTime): DateTime\Builder<this::TDateTime>) $operation,
    this::TDateTime $expected,
  ): void {
    $builder = $operation($original);
    expect($builder->isValid())->toBeFalse();
    expect(() ==> $builder->exactX())->toThrow(DateTime\Exception::class);
    expect($builder->closest()->getParts())->toEqual($expected->getParts());
  }
}
