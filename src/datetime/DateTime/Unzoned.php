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
 * A combination of date/time parts with no timezone associated. Therefore, not
 * actually representing an "absolute" point in time, e.g. you can only
 * transform it to a DateTime\Timestamp if you provide a timezone.
 *
 * TODO: optional literal syntax: dt"2020-01-15 12:51"
 */
final class Unzoned extends DateTime {
  const type TComparableTo = this;

  //////////////////////////////////////////////////////////////////////////////
  // from X to this

  /**
   * Returns a `Builder` for an `Unzoned` instance with the specified date/time
   * parts. This returns a `Builder` instead of returning an `Unzoned` instance
   * directly, since the provided combination of date/time parts may be invalid
   * (e.g. `$day` out of range for the specified `$month`).
   */
  public static function fromParts(
    int $year,
    int $month,
    int $day,
    int $hours = 0,
    int $minutes = 0,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): Builder<this> {
    return new _DateTime\UnzonedBuilder(
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
   * Returns an `Unzoned` instance with the specified date/time parts. Throws if
   * the provided combination of date/time parts is invalid (e.g. `$day` out of
   * range for the specified `$month`).
   *
   * Equivalent to `Unzoned::fromParts(...)->exactX()`
   */
  public static function fromPartsX(
    int $year,
    int $month,
    int $day,
    int $hours = 0,
    int $minutes = 0,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): this {
    return self::fromPartsXImpl(
      Zone::UTC,
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
   * Returns an instance matching the date/time from the provided string. This
   * requires specifying a valid timezone, since the date/time string may be in
   * a format that would otherwise be ambiguous (e.g. "3 days ago").
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
    ?Unzoned $relative_to = null,
  ): this {
    return self::fromTimestampImpl(
      $timezone,
      Timestamp::parse($timezone, $str, $relative_to?->timestamp),
    );
  }

  //////////////////////////////////////////////////////////////////////////////
  // from this to X

  /**
   * Converts this `Unzoned` instance to a `Zoned` instance with the same
   * date/time parts, in the specified timezone. Returns a `Builder`, since this
   * combination of date/time parts may be invalid in the specified timezone
   * (e.g. 2:30am on a DST change day).
   */
  public function withTimezone(Zone $timezone): Builder<Zoned> {
    return Zoned::fromParts($timezone, ...$this->getParts());
  }

  /**
   * Converts this instance to a string using the provided
   * strftime(3)-compatible format string.
   */
  public function format(UnzonedDateFormatString $format_string): string {
    using new _DateTime\ZoneOverride($this->timezone);
    return \strftime($format_string as string, $this->timestamp->getSeconds());
  }

  //////////////////////////////////////////////////////////////////////////////
  // getters

  <<__Override>>
  public function getWeekday(): Weekday {
    return (int)$this->format('%u') as Weekday;
  }

  <<__Override>>
  protected function getISOWeekNumberImpl(): int {
    return (int)$this->format('%V');
  }

  //////////////////////////////////////////////////////////////////////////////
  // compare

  <<__Override>>
  public function compare(this $other): int {
    return $this->timestamp->compare($other->timestamp);
  }

  <<__Override>>
  public function timeSince(this $other): Time {
    return $this->timestamp->timeSince($other->timestamp);
  }

  //////////////////////////////////////////////////////////////////////////////
  // internals

  <<__Override>>
  protected static function builderFromParts(
    Zone $_,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): Builder<this> {
    return new _DateTime\UnzonedBuilder(
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
