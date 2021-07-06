<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\_Private\_DateTime;
use namespace HH\Lib\Experimental\DateTime;

/**
 * Shared logic for DateTime\DateTime objects and DateTime\Builder objects, all
 * of which store and manipulate individual date/time parts.
 */
trait HasParts<T as DateTime\DateTime> {

  protected DateTime\Zone $timezone;
  protected int $year;
  protected int $month;
  protected int $day;
  protected int $hours;
  protected int $minutes;
  protected int $seconds;
  protected int $nanoseconds;

  abstract protected static function builderFromParts(
    DateTime\Zone $timezone,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): DateTime\Builder<T>;

  final public function withDate(
    int $year,
    int $month,
    int $day,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $year,
      $month,
      $day,
      $this->hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  final public function withTime(
    int $hours,
    int $minutes,
    int $seconds = 0, // if omitted, reset to zero, do not preserve
    int $nanoseconds = 0,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $hours,
      $minutes,
      $seconds,
      $nanoseconds,
    );
  }

  final public function withYear(int $year): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $year,
      $this->month,
      $this->day,
      $this->hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  final public function withMonth(
    int $month,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $month,
      $this->day,
      $this->hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  final public function withDay(int $day): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $this->month,
      $day,
      $this->hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  final public function withHours(
    int $hours,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  final public function withMinutes(
    int $minutes,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $this->hours,
      $minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  final public function withSeconds(
    int $seconds,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $this->hours,
      $this->minutes,
      $seconds,
      $this->nanoseconds,
    );
  }

  final public function withNanoseconds(
    int $nanoseconds,
  ): DateTime\Builder<T> {
    return static::builderFromParts(
      $this->timezone,
      $this->year,
      $this->month,
      $this->day,
      $this->hours,
      $this->minutes,
      $this->seconds,
      $nanoseconds,
    );
  }
}
