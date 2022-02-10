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

use namespace HH\Lib\Str;
use namespace HH\Lib\Experimental\_Private\_DateTime;
use type HH\Lib\Experimental\Time;

/**
 * A set of date/time parts associated with a timezone. Unlike DateTime\Unzoned
 * objects, this represents an "absolute" point in time and can be converted to
 * a DateTime\Timestamp unambiguously.
 */
final class Zoned extends DateTime implements Instant {
  const type TFormat = _DateTime\ZonedDateFormat;
  const type TFormatString = ZonedDateFormatString;

  //////////////////////////////////////////////////////////////////////////////
  // from X to this

  /**
   * Returns an instance representing the current date and time in the specified
   * timezone.
   */
  public static function now(Zone $timezone): this {
    return self::fromTimestamp($timezone, Timestamp::now());
  }

  /**
   * Returns a `Builder` for an instance representing the specified time on the
   * current day in the specified timezone.
   *
   * Equivalent to `Zoned::now($timezone)->withTime(...)`
   */
  public static function todayAt(
    Zone $timezone,
    int $hours,
    int $minutes,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): Builder<this> {
    return self::now($timezone)
      ->withTime($hours, $minutes, $seconds, $nanoseconds);
  }

  /**
   * Returns a `Builder` for a `Zoned` instance with the specified date/time
   * parts. This returns a `Builder` instead of returning a `Zoned` instance
   * directly, since the provided combination of date/time parts may be invalid
   * (e.g. `$day` out of range for the specified `$month`).
   *
   * If the date/time is ambiguous (e.g. 2:30am may occur twice on a DST change
   * day), it returns the earlier one. Use `->plusHours(1)` to get the later
   * one.
   */
  public static function fromParts(
    Zone $timezone,
    int $year,
    int $month,
    int $day,
    int $hours = 0,
    int $minutes = 0,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): Builder<this> {
    return new _DateTime\ZonedBuilder(
      $timezone,
      $year,
      $month,
      $day,
      $hours,
      $minutes,
      $seconds,
      $nanoseconds,
    );
  }

  /**
   * Returns a `Zoned` instance with the specified date/time parts. Throws if
   * the provided combination of date/time parts is invalid (e.g. `$day` out of
   * range for the specified `$month`).
   *
   * Equivalent to `Zoned::fromParts(...)->exactX()`
   */
  public static function fromPartsX(
    Zone $timezone,
    int $year,
    int $month,
    int $day,
    int $hours = 0,
    int $minutes = 0,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): this {
    return self::fromPartsXImpl(
      $timezone,
      $year,
      $month,
      $day,
      $hours,
      $minutes,
      $seconds,
      $nanoseconds,
    );
  }

  /**
   * Returns a `Zoned` instance representing the same point in time as the
   * provided `Timestamp`. The date/time parts will be calculated for the
   * specified timezone.
   */
  public static function fromTimestamp(
    Zone $timezone,
    Timestamp $timestamp,
  ): this {
    return self::fromTimestampImpl($timezone, $timestamp);
  }

  /**
   * Returns an instance matching the date/time from the provided string.
   *
   * An optional `$relative_to` argument can be provided, which will be used if
   * the input string is relative like "3 days ago" or "12:30 today". If not
   * provided, `$relative_to` defaults to the current time.
   *
   * @see https://www.php.net/manual/en/datetime.formats.php
   */
  public static function parse(
    Zone $timezone,
    string $str,
    ?Instant $relative_to = null,
  ): this {
    return Zoned::fromTimestampImpl(
      $timezone,
      Timestamp::parse($timezone, $str, $relative_to?->getTimestamp()),
    );
  }

  //////////////////////////////////////////////////////////////////////////////
  // from this to X

  /**
   * Returns a `Timestamp` representing the same point in time as this `Zoned`
   * instance.
   */
  public function getTimestamp(): Timestamp {
    return $this->timestamp;
  }

  /**
   * Returns an `Unzoned` instance with all date/time parts equal to this
   * `Zoned` instance.
   */
  public function withoutTimezone(): Unzoned {
    return Unzoned::fromParts(...$this->getParts())->exactX();
  }

  /**
   * Returns a new `Zoned` instance representing the same point in time as this
   * `Zoned` instance, but with date/time parts adjusted for the specified
   * timezone.
   */
  public function convertToTimezone(Zone $timezone): this {
    return self::fromTimestamp($timezone, $this->timestamp);
  }

  /**
   * Converts this instance to a string using the provided
   * strftime(3)-compatible format string.
   */
  public function format(ZonedDateFormatString $format_string): string {
    using new _DateTime\ZoneOverride($this->timezone);
    return \strftime($format_string as string, $this->timestamp->getSeconds());
  }

  //////////////////////////////////////////////////////////////////////////////
  // getters

  public function getTimezone(): Zone {
    return $this->timezone;
  }

  /**
   * Returns the offset from UTC for the timezone stored in this instance that
   * applies on the date/time stored in this instance (this will be either the
   * stored timezone's standard offset or its DST offset depending on whether
   * the stored date/time is during DST).
   */
  public function getTimezoneOffset(): Time {
    using new _DateTime\ZoneOverride($this->getTimezone());
    return Time::seconds((int)\date('Z', $this->getTimestamp()->getSeconds()));
  }

  <<__Override>>
  public function getWeekday(): Weekday {
    return (int)$this->format('%u') as Weekday;
  }

  /**
   * Returns true if the date/time stored in this instance is during Daylight
   * Savings Time (summer time) in the timezone stored in this instance.
   */
  public function isDST(): bool {
    using new _DateTime\ZoneOverride($this->getTimezone());
    return \date('I', $this->getTimestamp()->getSeconds()) === '1';
  }

  <<__Override>>
  protected function getISOWeekNumberImpl(): int {
    return (int)$this->format('%V');
  }

  //////////////////////////////////////////////////////////////////////////////
  // comparisons

  <<__Override>>
  public function compare(Instant $other): int {
    return $this->timestamp->compare($other->getTimestamp());
  }

  /**
   * Returns true iff the provided object represents the same point in time and
   * has the same timezone. Implies that all date/time parts as well as the
   * timestamp of the two objects are equal.
   *
   * See also isAtTheSameTime().
   *
   * To compare date/time parts ignoring timezones, use:
   *   $a->withoutTimezone()->isAtTheSameTime($b->withoutTimezone())
   */
  public function isEqualIncludingTimezone(this $other): bool {
    return $this->isAtTheSameTime($other) &&
      $this->timezone === $other->timezone;
  }

  //////////////////////////////////////////////////////////////////////////////
  // operations

  <<__Override>>
  public function timeSince(Instant $other): Time {
    return $this->timestamp->timeSince($other);
  }

  //////////////////////////////////////////////////////////////////////////////
  // internals

  <<__Override>>
  protected static function builderFromParts(
    Zone $timezone,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): Builder<this> {
    return new _DateTime\ZonedBuilder(
      $timezone,
      $year,
      $month,
      $day,
      $hours,
      $minutes,
      $seconds,
      $nanoseconds,
    );
  }
}
