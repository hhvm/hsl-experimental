<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Time_EXPERIMENTAL;

/**
 * Returns a Unix timestamp representing the instant given by the calendar/clock
 * values and Timezone, or null if the time is invalid.
 */
<<__Rx>>
function local_time_to_unix_timestamp(
  resource $timezone,
  int $year,
  int $month,
  int $day,
  int $hour,
  int $minute,
  int $second,
  int $nanosecond,
): ?int {
  invariant_violation('future builtin');
}

/**
 * Returns an tuple of calendar/clock values representing the given instant in
 * the given Timezone.
 */
<<__Rx>>
function unix_timestamp_to_local_time(
  resource $timezone,
  int $unix_timestamp,
): (int, int, int, int, int, int, int) {
  invariant_violation('future builtin');
}

// <<__Const>>
abstract class DateTimeBase {

  const int NS_IN_US = 1000;
  const int US_IN_MS = 1000;
  const int NS_IN_MS = self::NS_IN_US * self::US_IN_MS;
  const int NS_IN_S = 1000 * self::NS_IN_MS;
  const int NS_IN_M = 60 * self::NS_IN_S;
  const int NS_IN_H = 60 * self::NS_IN_M;
  const int NS_IN_DAY = 24 * self::NS_IN_H;
  const int NS_IN_WEEK = 7 * self::NS_IN_DAY;

  final private function __construct(
    protected Timezone $timezone,
    protected int $unixTimestamp,
    private int $year,
    private int $month,
    private int $day,
    private int $hour,
    private int $minute,
    private int $second,
    private int $nanosecond,
  ) {}

  final protected static function __createFromValues(
    Timezone $timezone,
    int $year,
    int $month,
    int $day,
    int $hour,
    int $minute,
    int $second,
    int $nanosecond,
  ): this {
    $unix_timestamp = local_time_to_unix_timestamp(
      $timezone->__getData(),
      $year,
      $month,
      $day,
      $hour,
      $minute,
      $second,
      $nanosecond,
    );
    invariant(
      $unix_timestamp !== null,
      '%4d-%2d-%2d %2d:%2d:%2d.%9d '.
      'is not a valid date/time in the %s timezone',
      $year,
      $month,
      $day,
      $hour,
      $minute,
      $second,
      $nanosecond,
      $timezone->toString(),
    );
    return new static(
      $timezone,
      $unix_timestamp,
      $year,
      $month,
      $day,
      $hour,
      $minute,
      $second,
      $nanosecond,
    );
  }

  final protected static function __createFromTimestamp(
    Timezone $timezone,
    int $unix_timestamp,
  ): this {
    return new static(
      $timezone,
      $unix_timestamp,
      ...unix_timestamp_to_local_time(
        $timezone->__getData(),
        $unix_timestamp,
      ),
    );
  }



  final public function getNanosecond(): int {
    return $this->nanosecond;
  }

  final public function getSecond(): int {
    return $this->second;
  }

  final public function getMinute(): int {
    return $this->minute;
  }

  final public function getHour(): int {
    return $this->hour;
  }

  final public function getDay(): int {
    return $this->day;
  }

  final public function getMonth(): int {
    return $this->month;
  }

  final public function getYear(): int {
    return $this->year;
  }



  final public function withNanosecondX(int $nanosecond): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $this->hour,
      $this->minute,
      $this->second,
      static::validateNS($nanosecond),
    );
  }

  final public function withSecondX(int $second): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $this->hour,
      $this->minute,
      static::validateS($second),
      $this->nanosecond,
    );
  }

  final public function withMinuteX(int $minute): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $this->hour,
      static::validateM($minute),
      $this->second,
      $this->nanosecond,
    );
  }

  final public function withHourX(int $hour): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      static::validateH($hour),
      $this->minute,
      $this->second,
      $this->nanosecond,
    );
  }

  final public function withDayX(int $day): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      $this->month,
      static::validateDay($day),
      $this->hour,
      $this->minute,
      $this->second,
      $this->nanosecond,
    );
  }

  final public function withMonthX(int $month): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      static::validateMonth($month),
      $this->day,
      $this->hour,
      $this->minute,
      $this->second,
      $this->nanosecond,
    );
  }

  final public function withYearX(int $year): this {
    return static::__createFromValues(
      $this->timezone,
      static::validateYear($year),
      $this->month,
      $this->day,
      $this->hour,
      $this->minute,
      $this->second,
      $this->nanosecond,
    );
  }

  final public function withDateX(int $year, int $month, int $day): this {
    return static::__createFromValues(
      $this->timezone,
      static::validateYear($year),
      static::validateMonth($month),
      static::validateDay($day),
      $this->hour,
      $this->minute,
      $this->second,
      $this->nanosecond,
    );
  }

  final public function withTimeX(int $hour, int $minute, int $second): this {
    return static::__createFromValues(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      static::validateH($hour),
      static::validateM($minute),
      static::validateS($second),
      $this->nanosecond,
    );
  }



  final public function plusNanoseconds(int $nanoseconds): this {
    if ($nanoseconds > 0) {
      invariant(
        \PHP_INT_MAX - $nanoseconds >= $this->unixTimestamp,
        'Exceeded the latest representable %s',
        static::class,
      );
    } else {
      invariant(
        \PHP_INT_MIN - $nanoseconds <= $this->unixTimestamp,
        'Exceeded the earliest representable %s',
        static::class,
      );
    }
    return static::__createFromTimestamp(
      $this->timezone,
      $this->unixTimestamp + $nanoseconds,
    );
  }

  final public function plusMicroseconds(int $microseconds): this {
    return $this->plusNanoseconds($microseconds * self::NS_IN_US);
  }

  final public function plusMilliseconds(int $milliseconds): this {
    return $this->plusNanoseconds($milliseconds * self::NS_IN_MS);
  }

  final public function plusSeconds(int $seconds): this {
    return $this->plusNanoseconds($seconds * self::NS_IN_S);
  }

  final public function plusMinutes(int $minutes): this {
    return $this->plusNanoseconds($minutes * self::NS_IN_M);
  }

  final public function plusHours(int $hours): this {
    return $this->plusNanoseconds($hours * self::NS_IN_H);
  }

  final public function plusDays(int $days): this {
    return $this->plusNanoseconds($days * self::NS_IN_DAY);
  }

  final public function plusWeeks(int $weeks): this {
    return $this->plusNanoseconds($weeks * self::NS_IN_WEEK);
  }

  final public function minusNanoseconds(int $nanoseconds): this {
    return $this->plusNanoseconds(-$nanoseconds);
  }

  final public function minusMicroseconds(int $microseconds): this {
    return $this->plusMicroseconds(-$microseconds);
  }

  final public function minusMilliseconds(int $milliseconds): this {
    return $this->plusMilliseconds(-$milliseconds);
  }

  final public function minusSeconds(int $seconds): this {
    return $this->plusSeconds(-$seconds);
  }

  final public function minusMinutes(int $minutes): this {
    return $this->plusMinutes(-$minutes);
  }

  final public function minusHours(int $hours): this {
    return $this->plusHours(-$hours);
  }

  final public function minusDays(int $days): this {
    return $this->plusDays(-$days);
  }

  final public function minusWeeks(int $weeks): this {
    return $this->plusWeeks(-$weeks);
  }



  final protected static function validateNS(int $nanosecond): int {
    invariant(
      $nanosecond >= 0 && $nanosecond < self::NS_IN_S,
      'Nanosecond value (%d) is out of bounds',
      $nanosecond,
    );
    return $nanosecond;
  }

  final protected static function validateS(int $second): int {
    invariant(
      $second >= 0 && $second <= 59,
      'Second value (%d) is out of bounds',
      $second,
    );
    return $second;
  }

  final protected static function validateM(int $minute): int {
    invariant(
      $minute >= 0 && $minute <= 59,
      'Minute value (%d) is out of bounds',
      $minute,
    );
    return $minute;
  }

  final protected static function validateH(int $hour): int {
    invariant(
      $hour >= 0 && $hour <= 23,
      'Hour value (%d) is out of bounds',
      $hour,
    );
    return $hour;
  }

  final protected static function validateDay(int $day): int {
    invariant(
      $day >= 1 && $day <= 31,
      'Day value (%d) is out of bounds',
      $day,
    );
    return $day;
  }

  final protected static function validateMonth(int $month): int {
    invariant(
      $month >= 1 && $month <= 12,
      'Month value (%d) is out of bounds',
      $month,
    );
    return $month;
  }

  final protected static function validateYear(int $year): int {
    // TODO(kunalm): These should probably be runtime-provided constants,
    // since they're dependent on the size of integers.
    invariant(
      $year >= 1677 && $year <= 2262,
      'Year value (%d) is out of bounds',
      $year,
    );
    return $year;
  }

}
