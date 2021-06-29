<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\MonoTime;
use namespace HH\Lib\Experimental\_Private\_DateTime;
use type HH\Lib\Experimental\Time;

/**
 * Represents a point in time on the monotonic clock (aka `steady_clock` in
 * C++), with nanosecond precision. This is ideal for measuring time intervals
 * (by comparing instances created at different times).
 *
 * The monotonic clock is guaranteed to always move forward at a constant
 * rate, unlike standard (wall time) clock, which can move backwards or skip
 * forward, e.g. during DST changes or when the user adjusts it.
 *
 * The monotonic clock is not guaranteed to relate to the wall time clock in any
 * way (e.g. it can be something like time since the last reboot), which is why
 * there is no way to convert between `MonoTime\Timestamp` and
 * `DateTime\Timestamp` or `DateTime\DateTime` objects.
 */
final class Timestamp extends _DateTime\Timestamp {
  const type TComparableTo = this;

  /**
   * Returns a `MonoTime\Timestamp` instance representing the current time on
   * the monotonic clock.
   */
  public static function now(): this {
    return static::fromRaw(0, \clock_gettime_ns(\CLOCK_MONOTONIC));
  }

  <<__Override>>
  public function compare(this $other): int {
    $a = $this->toRaw();
    $b = $other->toRaw();
    return $a[0] !== $b[0] ? $a[0] <=> $b[0] : $a[1] <=> $b[1];
  }

  /**
   * Returns a `Time` instance representing the amount of time elapsed between
   * the provided `MonoTime\Timestamp` and the timestamp represented by the
   * current instance (`$this`).
   *
   * The result can be negative or zero if `$other` is not before `$this`.
   */
  <<__Override>>
  public function timeSince(this $other): Time {
    $a = $this->toRaw();
    $b = $other->toRaw();
    return Time::fromParts(0, 0, $a[0] - $b[0], $a[1] - $b[1]);
  }
}
