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
use type HH\Lib\Experimental\Time;

/**
 * Comparison and operation methods shared by all classes that represent points
 * in time (all timestamps as well as `DateTime\DateTime`).
 */
abstract class Comparable {
  abstract const type TComparableTo;

  //////////////////////////////////////////////////////////////////////////////
  // comparisons

  /**
   * Returns 1 if this instance represents a point in time after `$other`,
   * -1 if before, and 0 if they are equal.
   */
  abstract public function compare(this::TComparableTo $other): int;

  /**
   * Returns true iff the provided object represents the same point in time.
   * Implies that the timestamp of the two objects is equal, but for
   * `DateTime\Zoned` objects, some date/time parts may differ if both don't
   * have the same timezone.
   *
   * See also `DateTime\Zoned::isEqualIncludingTimezone()`.
   *
   * To compare `DateTime\Zoned` parts ignoring timezones, use:
   *   $a->withoutTimezone()->isAtTheSameTime($b->withoutTimezone())
   */
  final public function isAtTheSameTime(this::TComparableTo $other): bool {
    return $this->compare($other) === 0;
  }

  // note: 3pm in some timezone may be later than 2pm in another
  final public function isBefore(this::TComparableTo $other): bool {
    return $this->compare($other) < 0;
  }

  final public function isBeforeOrAtTheSameTime(
    this::TComparableTo $other,
  ): bool {
    return $this->compare($other) <= 0;
  }

  final public function isAfter(this::TComparableTo $other): bool {
    return $this->compare($other) > 0;
  }

  final public function isAfterOrAtTheSameTime(
    this::TComparableTo $other,
  ): bool {
    return $this->compare($other) >= 0;
  }

  /**
   * Returns true if this instance represents a point in time after `$a` but
   * before `$b`, or vice-versa (before `$a` but after `$b`), or if this
   * instance is equal to `$a` and/or `$b`. Returns false if this instance is
   * before or after both `$a` and `$b`.
   */
  final public function isBetweenInclusive(
    this::TComparableTo $a,
    this::TComparableTo $b,
  ): bool {
    $a = $this->compare($a);
    $b = $this->compare($b);
    return $a === 0 || $a !== $b;
  }

  /**
   * Returns true if this instance represents a point in time after `$a` but
   * before `$b`, or vice-versa (before `$a` but after `$b`). Returns false if
   * this instance is equal to `$a` and/or `$b`, or if this instance is before
   * or after both `$a` and `$b`.
   */
  final public function isBetweenExclusive(
    this::TComparableTo $a,
    this::TComparableTo $b,
  ): bool {
    $a = $this->compare($a);
    $b = $this->compare($b);
    return $a !== 0 && $b !== 0 && $a !== $b;
  }

  //////////////////////////////////////////////////////////////////////////////
  // operations

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified amount of time *after* the current instance (or *before*, if the
   * provided time interval is negative).
   */
  abstract public function plus(Time $time): this;

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified amount of time *before* the current instance (or *after*, if the
   * provided time interval is negative).
   */
  abstract public function minus(Time $time): this;

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of hours *after* the current instance (or *before*, if the
   * specified number of hours is negative).
   */
  final public function plusHours(int $hours): this {
    return $this->plus(Time::hours($hours));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of minutes *after* the current instance (or *before*, if
   * the specified number of minutes is negative).
   */
  final public function plusMinutes(int $minutes): this {
    return $this->plus(Time::minutes($minutes));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of seconds *after* the current instance (or *before*, if
   * the specified number of seconds is negative).
   */
  final public function plusSeconds(int $seconds): this {
    return $this->plus(Time::seconds($seconds));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of nanoseconds *after* the current instance (or *before*,
   * if the specified number of nanoseconds is negative).
   */
  final public function plusNanoseconds(int $nanoseconds): this {
    return $this->plus(Time::nanoseconds($nanoseconds));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of hours *before* the current instance (or *after*, if the
   * specified number of hours is negative).
   */
  final public function minusHours(int $hours): this {
    return $this->minus(Time::hours($hours));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of minutes *before* the current instance (or *after*, if
   * the specified number of minutes is negative).
   */
  final public function minusMinutes(int $minutes): this {
    return $this->minus(Time::minutes($minutes));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of seconds *before* the current instance (or *after*, if
   * the specified number of seconds is negative).
   */
  final public function minusSeconds(int $seconds, int $nanoseconds = 0): this {
    return $this->minus(Time::seconds($seconds));
  }

  /**
   * Returns a new instance representing the point in time that is exactly the
   * specified number of nanoseconds *before* the current instance (or *after*,
   * if the specified number of nanoseconds is negative).
   */
  final public function minusNanoseconds(int $nanoseconds): this {
    return $this->minus(Time::nanoseconds($nanoseconds));
  }

  /**
   * Returns a `Time` instance representing the amount of time elapsed between
   * the provided instance and the current instance (`$this`).
   *
   * The result can be negative or zero if `$other` is not before `$this`.
   */
  abstract public function timeSince(this::TComparableTo $other): Time;
}
