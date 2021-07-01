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
 * Represents a point in time on the regular (wall time) clock, with nanosecond
 * precision.
 *
 * Unlike the monotonic clock (see `MonoTime\Timestamp`), wall time is not
 * guaranteed to always move forward at a constant rate. It can move backwards
 * or skip forward, e.g. during DST changes or when the user adjusts it.
 * Therefore, `MonoTime\Timestamp` can be more appropriate to use e.g. when
 * measuring elapsed time.
 *
 * `DateTime\Timestamp` instances can be converted to an equivalent
 * `DateTime\Zoned` object by specifying any valid timezone. Both of these types
 * implement the shared interface `Instant`, and can therefore be used
 * interchangeably in many places.
 */
final class Timestamp extends _DateTime\Timestamp implements Instant {

  /**
   * Returns a `DateTime\Timestamp` instance representing the current time on
   * the regular (wall time) clock.
   */
  public static function now(): this {
    return self::fromRaw(0, \clock_gettime_ns(\CLOCK_REALTIME));
  }

  //////////////////////////////////////////////////////////////////////////////
  // conversions required by the Instant interface

  public function getTimestamp(): this {
    return $this;
  }

  /* TODO
  public function convertToTimezone(Zone $zone): Zoned {
    return Zoned::fromTimestamp($zone, $this);
  }
  */

  //////////////////////////////////////////////////////////////////////////////
  // comparisons

  <<__Override>>
  public function compare(Instant $other): int {
    $a = $this->toRaw();
    $b = $other->getTimestamp()->toRaw();
    return $a[0] !== $b[0] ? $a[0] <=> $b[0] : $a[1] <=> $b[1];
  }

  <<__Override>>
  public function timeSince(Instant $other): Time {
    $a = $this->toRaw();
    $b = $other->getTimestamp()->toRaw();
    return Time::fromParts(0, 0, $a[0] - $b[0], $a[1] - $b[1]);
  }

  //////////////////////////////////////////////////////////////////////////////
  // parse/format

  /**
   * Converts this timestamp to a string using the provided
   * strftime(3)-compatible format string. This requires specifying a valid
   * timezone, since the same timestamp represents different times/dates in
   * different timezones.
   */
  public function format(
    Zone $timezone,
    // TODO: locale?
    ZonedDateFormatString $format_string,
  ): string {
    using new _DateTime\ZoneOverride($timezone);
    // The nanosecond part is ignored because strftime doesn't have any %
    // specifiers that would use it.
    return \strftime($format_string as string, $this->getSeconds());
  }

  /**
   * Returns an instance matching the date/time from the provided string. This
   * requires specifying a valid timezone, since the same date/time string
   * represents a different point in time in each timezone. But note that if the
   * provided `$raw_string` itself contains a timezone, this takes precedence
   * over the `$timezone` argument.
   *
   * An optional `$relative_to` argument can be provided, which will be used if
   * the input string is relative like "3 days ago" or "12:30 today". If not
   * provided, `$relative_to` defaults to the current time.
   *
   * @see https://www.php.net/manual/en/datetime.formats.php
   */
  public static function parse(
    Zone $timezone,
    string $raw_string,
    ?Instant $relative_to = null,
  ): this {
    using new _DateTime\ZoneOverride($timezone);
    if ($relative_to is nonnull) {
      $relative_to = $relative_to->getTimestamp()->getSeconds();
    }
    $raw = \strtotime($raw_string, $relative_to);
    if ($raw === false) {
      throw new Exception('Failed to parse date/time: %s', $raw_string);
    }
    return self::fromRaw($raw);
  }
}
