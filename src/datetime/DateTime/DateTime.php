<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\DateTime;
use namespace HH\Lib\Experimental\_Private\_DateTime;
use type HH\Lib\Experimental\Time;

/**
 * Base class for DateTime\Zoned and DateTime\Unzoned.
 */
<<__ConsistentConstruct>>
abstract class DateTime extends _DateTime\Comparable {
  use _DateTime\HasParts<this>;

  //////////////////////////////////////////////////////////////////////////////
  // getters

  final public function getYear(): int {
    return $this->year;
  }

  final public function getMonth(): int {
    return $this->month;
  }

  final public function getDay(): int {
    return $this->day;
  }

  /**
   * Hours are returned in the 24-hour format (0 to 23).
   * For the 12-hour format, see `getHoursAmPm()`.
   */
  final public function getHours(): int {
    return $this->hours;
  }

  final public function getMinutes(): int {
    return $this->minutes;
  }

  final public function getSeconds(): int {
    return $this->seconds;
  }

  final public function getNanoseconds(): int {
    return $this->nanoseconds;
  }

  /**
   * Returns (year, month, day) (big endian).
   */
  final public function getDate(): (int, int, int) {
    return tuple($this->year, $this->month, $this->day);
  }

  /**
   * Returns (hours, minutes, seconds, nanoseconds) (big endian).
   */
  final public function getTime(): (int, int, int, int) {
    return tuple(
      $this->hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  /**
   * Returns (year, month, day, hours, minutes, seconds, ns) (big endian).
   */
  final public function getParts(): (int, int, int, int, int, int, int) {
    return tuple(
      $this->year,
      $this->month,
      $this->day,
      $this->hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  //////////////////////////////////////////////////////////////////////////////
  // fancy getters

  // I expect a common use case might be constructing a DateTime object just to
  // call one of these: DateTime\Unzoned::fromParts($y, $m, $d)->getWeekday()

  /**
   * Returns the century number for the year stored in this DateTime object,
   * e.g. returns 20 for years 1900 to 1999.
   */
  final public function getCentury(): int {
    return (int)($this->year / 100) + 1;
  }

  /**
   * Returns the number of days for the month stored in this DateTime object,
   * correctly taking into account whether the year stored in this DateTime
   * object is a leap year.
   */
  final public function getDaysInMonth(): int {
    return _DateTime\days_in_month($this->year, $this->month);
  }

  /**
   * Similar to `getHours()`, but uses the 12-hour format (1 to 12).
   */
  final public function getHoursAmPm(): (int, AmPm) {
    return tuple(
      $this->hours % 12 ?: 12,
      $this->hours < 12 ? AmPm::AM : AmPm::PM,
    );
  }

  /**
   * Returns the week number (1 to 53) as specified by ISO-8601 for the date
   * stored in this DateTime object. Returns the tuple (year, week number) where
   * year is usually equal to `$this->getYear()`, but not always -- the first 3
   * days of each year may fall into the last week of the previous year, and the
   * last 3 days of each year may fall into the first week of the next year. For
   * example:
   *
   * - for 2020-01-01, returns (2020, 1) -- the first week of 2020
   * - for 2021-01-01, returns (2020, 53), as this day falls into the last week
   *   of the previous year
   */
  final public function getISOWeekNumber(): (int, int) {  // year, week number
    $year = $this->year;
    $week = $this->getISOWeekNumberImpl();
    // the current day may be part of the first/last ISO week of the
    // next/previous year
    if ($this->month === 12 && $week === 1) {
      ++$year;
    } else if ($this->month === 1 && $week > 50) {
      --$year;
    }
    return tuple($year, $week);
  }

  /**
   * Returns the year in the short format, discarding any but the last 2 digits
   * (e.g. 99 for 1999, or 4 for 2004).
   */
  final public function getYearShort(): int {
    return $this->year % 100;
  }

  /**
   * Returns the weekday for the date stored in this DateTime object, as one of
   * the `DateTime\Weekday` enum values. If used as `int`, the enum value is
   * between 1 for Monday to 7 for Sunday (compliant with ISO-8601).
   */
  abstract public function getWeekday(): Weekday;

  /**
   * Returns whether the year stored in this DateTime object is a leap year
   * (has 366 days including February 29).
   */
  final public function isLeapYear(): bool {
    return _DateTime\is_leap_year($this->year);
  }

  abstract protected function getISOWeekNumberImpl(): int;

  //////////////////////////////////////////////////////////////////////////////
  // plus/minus time intervals (result always valid, no Builder needed)

  <<__Override>>
  final public function plus(Time $time): this {
    return static::fromTimestampImpl(
      $this->timezone,
      $this->timestamp->plus($time),
    );
  }

  <<__Override>>
  final public function minus(Time $time): this {
    return static::fromTimestampImpl(
      $this->timezone,
      $this->timestamp->minus($time),
    );
  }

  //////////////////////////////////////////////////////////////////////////////
  // plus/minus date intervals (result may be invalid, so returns a Builder)

  /**
   * Returns a builder for a DateTime instance representing the same month/day
   * (as well as the same time), but the specified number of `$years` later
   * (e.g. `plusYears(1)` for the same day next year). This returns a `Builder`,
   * as the resulting DateTime instance could be invalid (e.g. February 29 of a
   * leap year + 1 year).
   */
  final public function plusYears(int $years): Builder<this> {
    return $this->withYear($this->year + $years);
  }

  /**
   * Returns a builder for a DateTime instance representing the same month/day
   * (as well as the same time), but the specified number of `$years` earlier
   * (e.g. `minusYears(1)` for the same day last year). This returns a
   * `Builder`, as the resulting DateTime instance could be invalid (e.g.
   * February 29 of a leap year minus 1 year).
   */
  final public function minusYears(int $years): Builder<this> {
    return $this->withYear($this->year - $years);
  }

  /**
   * Returns a builder for a DateTime instance representing the same day (as
   * well as the same time), but the specified number of `$months` later (e.g.
   * `plusMonths(1)` for the same day next month). This returns a `Builder`, as
   * the resulting DateTime instance could be invalid (e.g.
   * January 31st + 1 month).
   */
  final public function plusMonths(int $months): Builder<this> {
    if ($months < 0) {
      return $this->minusMonths(-$months);
    }
    $new_month_raw = $this->month + $months - 1;
    return $this->withDate(
      $this->year + (int)($new_month_raw / 12),
      $new_month_raw % 12 + 1,
      $this->day,
    );
  }

  /**
   * Returns a builder for a DateTime instance representing the same day (as
   * well as the same time), but the specified number of `$months` earlier (e.g.
   * `minusMonths(1)` for the same day last month). This returns a `Builder`, as
   * the resulting DateTime instance could be invalid (e.g. March 31st minus
   * 1 month).
   */
  final public function minusMonths(int $months): Builder<this> {
    if ($months < 0) {
      return $this->plusMonths(-$months);
    }
    $new_month_raw = $this->month - $months - 12;
    return $this->withDate(
      $this->year + (int)($new_month_raw / 12),
      $new_month_raw % 12 + 12,
      $this->day,
    );
  }

  /**
   * Returns a builder for a DateTime instance representing the same time, but
   * the specified number of `$days` later (e.g. `plusDays(1)` for the same time
   * tomorrow). This returns a `Builder`, as the resulting DateTime instance
   * could be invalid (e.g. 2:30am on a day when DST change happens).
   */
  final public function plusDays(int $days): Builder<this> {
    // Doing this calculation in UTC to avoid DST issues (0:30am on DST change
    // day + 24 hours = 23:30 on the same day)
    return $this->withDate(
      ...Zoned::fromParts(Zone::UTC, $this->year, $this->month, $this->day)
        ->exactX()
        ->plusHours($days * 24)
        ->getDate()
    );
  }

  /**
   * Returns a builder for a DateTime instance representing the same time, but
   * the specified number of `$days` earlier (e.g. `minusDays(1)` for the same
   * time yesterday). This returns a `Builder`, as the resulting DateTime
   * instance could be invalid (e.g. 2:30am on a day when DST change happens).
   */
  final public function minusDays(int $days): Builder<this> {
    return $this->plusDays(-$days);
  }

  //////////////////////////////////////////////////////////////////////////////
  // internals

  final protected static function fromPartsXImpl(
    Zone $timezone,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): this {
    using new _DateTime\ZoneOverride($timezone);
    $timestamp = Timestamp::fromRaw(
      \mktime($hours, $minutes, $seconds, $month, $day, $year),
      $nanoseconds,
    );
    $ret = new static(
      $timezone,
      $timestamp,
      $year,
      $month,
      $day,
      $hours,
      $minutes,
      $seconds,
      $nanoseconds,
    );
    // mktime() doesn't throw on invalid date/time, but silently returns a
    // timestamp that doesn't match the input; so we check for that here.
    if (
      $ret->getParts() !==
        Zoned::fromTimestamp($timezone, $timestamp)->getParts()
    ) {
      throw new Exception('Date/time is not valid in this timezone.');
    }
    return $ret;
  }

  final protected static function fromTimestampImpl(
    Zone $timezone,
    Timestamp $timestamp,
  ): this {
    using new _DateTime\ZoneOverride($timezone);
    list($s, $ns) = $timestamp->toRaw();
    $parts = \getdate($s);
    return new static(
      $timezone,
      $timestamp,
      $parts['year'],
      $parts['mon'],
      $parts['mday'],
      $parts['hours'],
      $parts['minutes'],
      $parts['seconds'],
      $ns,
    );
  }

  <<__Override>>
  final protected function __construct(
    Zone $timezone,
    protected Timestamp $timestamp,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ) {
    if (!(
      $month >= 1 && $month <= 12 &&
      $day >= 1 && $day <= _DateTime\days_in_month($year, $month) &&
      $hours >= 0 && $hours < 24 &&
      $minutes >= 0 && $minutes < 60 &&
      $seconds >= 0 && $seconds < 60 &&  // leap seconds not supported
      $nanoseconds >= 0 && $nanoseconds < _DateTime\NS_IN_SEC
    )) {
      throw new Exception('Invalid date/time.');
    }
    $this->timezone = $timezone;
    $this->year = $year;
    $this->month = $month;
    $this->day = $day;
    $this->hours = $hours;
    $this->minutes = $minutes;
    $this->seconds = $seconds;
    $this->nanoseconds = $nanoseconds;
  }
}
