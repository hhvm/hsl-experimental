<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Time;

// <<__Const>>
final class LocalDateTime extends DateTimeBase {

  /**
   * Returns a new LocalDateTime that represents the given local time.
   */
  public static function fromValues(
    int $year = 1970,
    int $month = 1,
    int $day = 1,
    int $hour = 0,
    int $minute = 0,
    int $second = 0,
    int $nanosecond = 0,
  ): this {
    return static::__createFromValues(
      Timezone::UTC(),
      static::validateYear($year),
      static::validateMonth($month),
      static::validateDay($day),
      static::validateH($hour),
      static::validateM($minute),
      static::validateS($second),
      static::validateNS($nanosecond),
    );
  }

  /**
   * Returns a DateTime that represents the instant where the calendar/clock
   * values would be the same as this LocalDateTime in the given Timezone.
   *
   * For example, if this LocalDateTime represents "Jan 1 2018 1:00PM" and
   * withTimezoneX is called with the 'America/Los_Angeles' Timezone, then the
   * DateTime representing "Jan 1 2018 1:00PM" in the 'America/Los_Angeles'
   * timezone will be returned.
   *
   * If no such DateTime exists (e.g. due to a gap during DST transitions), or
   * if two such times exist (e.g. due to an overlap during DST transitions), an
   * exception will be thrown.
   *
   * Use this method when timezone-specific dates/times are expected.
   * For example, it would be appropriate to use this method to validate that
   * a time the user has entered in a form is valid.
   *
   * Do not use this method when timezone-agnostic dates/times are expected.
   * For example, if you were calculating the instant of an event that occurred
   * at 2:30AM Pacific time every day, this method would throw an exception on
   * the days of DST transitions.
   */
  public function withTimezoneX(Timezone $timezone): DateTime {
    return DateTime::fromValues(
      $timezone,
      $this->getYear(),
      $this->getMonth(),
      $this->getDay(),
      $this->getHour(),
      $this->getMinute(),
      $this->getSecond(),
      $this->getNanosecond(),
    );
  }

  /**
   * Returns whether this LocalDateTime is before the given LocalDateTime.
   */
  final public function isBefore(this $datetime): bool {
    return $this->unixTimestamp < $datetime->unixTimestamp;
  }

  /**
   * Returns whether this LocalDateTime is after the given LocalDateTime.
   */
  final public function isAfter(this $datetime): bool {
    return $this->unixTimestamp > $datetime->unixTimestamp;
  }

  /**
   * Returns whether this LocalDateTime is the same as the given LocalDateTime.
   */
  final public function isEqualTo(this $datetime): bool {
    return $this->unixTimestamp === $datetime->unixTimestamp;
  }

}
