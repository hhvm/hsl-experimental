<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib;

use const HH\Lib\DateTime\_Private\NS_IN_SEC;

/**
 * Represents a time interval (a specific number of hours, minutes, seconds, and
 * nanoseconds). May be positive or negative. All instances are immutable.
 *
 * To get an instance, call Time::hours(...), Time::minutes(...) etc. depending
 * on the largest time unit you want to specify. Each of these methods takes
 * all smaller units as optional arguments, e.g. Time::hours(2, 30, 15) returns
 * the interval "2hr 30min 15sec".
 *
 * All instances are normalized as follows:
 *
 * - all non-zero parts (hours, minutes, seconds, nanoseconds) will have the
 *   same sign
 * - minutes, seconds will be between -59 and 59
 * - nanoseconds will be between -999999999 and 999999999 (less than 1 second)
 *
 * For example, Time::hours(2, -183) normalizes to "-1hr -3min".
 */
final class Time {

  private function __construct(
    private int $hours,
    private int $minutes,
    private int $seconds,
    private int $nanoseconds,
  ) {}

  //////////////////////////////////////////////////////////////////////////////
  // factory methods

  /**
   * Returns an instance representing the specified number of hours (and
   * optionally minutes, seconds, nanoseconds). Due to normalization, the
   * actual values in the returned instance may differ from the provided ones.
   */
  public static function hours(
    int $hours,
    int $minutes = 0,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): this {
    return self::minutes(60 * $hours + $minutes, $seconds, $nanoseconds);
  }

  /**
   * Returns an instance representing the specified number of minutes (and
   * optionally seconds, nanoseconds). Due to normalization, the actual values
   * in the returned instance may differ from the provided ones, and the
   * resulting instance may contain larger units (e.g. `Time::minutes(63)` is
   * normalized to "1hr 3min").
   */
  public static function minutes(
    int $minutes,
    int $seconds = 0,
    int $nanoseconds = 0,
  ): this {
    return self::seconds(60 * $minutes + $seconds, $nanoseconds);
  }

  /**
   * Returns an instance representing the specified number of seconds (and
   * optionally nanoseconds). Due to normalization, the actual values in the
   * returned instance may differ from the provided ones, and the resulting
   * instance may contain larger units (e.g. `Time::seconds(63)` is normalized
   * to "1min 3sec").
   */
  public static function seconds(
    int $seconds,
    int $nanoseconds = 0,
  ): this {
    // This is where the normalization happens.
    $seconds += (int)($nanoseconds / NS_IN_SEC);
    $nanoseconds %= NS_IN_SEC;
    if ($seconds < 0 && $nanoseconds > 0) {
      ++$seconds;
      $nanoseconds -= NS_IN_SEC;
    } else if ($seconds > 0 && $nanoseconds < 0) {
      --$seconds;
      $nanoseconds += NS_IN_SEC;
    }
    $minutes = (int)($seconds / 60);
    $seconds %= 60;
    $hours = (int)($minutes / 60);
    $minutes %= 60;
    return new self($hours, $minutes, $seconds, $nanoseconds);
  }

  /**
   * Returns an instance representing the specified number of milliseconds (ms).
   * The value is converted and stored as nanoseconds, since that is the only
   * unit smaller than a second that we support. Due to normalization, the
   * resulting instance may contain larger units
   * (e.g. `Time::milliseconds(8042)` is normalized to "8sec 42000000ns").
   *
   * Note: 1 ms = 1/1000 s = 1000000 ns
   */
  public static function milliseconds(int $milliseconds): this {
    return self::seconds(0, 1000000 * $milliseconds);
  }

  /**
   * Returns an instance representing the specified number of microseconds (us).
   * The value is converted and stored as nanoseconds, since that is the only
   * unit smaller than a second that we support. Due to normalization, the
   * resulting instance may contain larger units
   * (e.g. `Time::microseconds(8000042)` is normalized to "8sec 42000ns").
   *
   * Note: 1 us = 1/1000000 s = 1000 ns
   */
  public static function microseconds(int $microseconds): this {
    return self::seconds(0, 1000 * $microseconds);
  }

  /**
   * Returns an instance representing the specified number of nanoseconds (ns).
   * Due to normalization, the resulting instance may contain larger units
   * (e.g. `Time::nanoseconds(8000000042)` is normalized to "8sec 42ns").
   *
   * Note: 1 s = 1000000000 ns (1e9)
   */
  public static function nanoseconds(int $nanoseconds): this {
    return self::seconds(0, $nanoseconds);
  }

  /**
   * Returns an instance with all parts equal to 0.
   */
  public static function zero(): this {
    return new self(0, 0, 0, 0);
  }

  //////////////////////////////////////////////////////////////////////////////
  // getters

  /**
   * Returns the "hours" part of this time interval (normalized as described,
   * and therefore may differ from the number of hours specified when this
   * instance was created).
   */
  public function getHours(): int {
    return $this->hours;
  }

  /**
   * Returns the "minutes" part of this time interval (normalized as described,
   * and therefore may differ from the number of minutes specified when this
   * instance was created).
   */
  public function getMinutes(): int {
    return $this->minutes;
  }

  /**
   * Returns the "seconds" part of this time interval (normalized as described,
   * and therefore may differ from the number of seconds specified when this
   * instance was created).
   */
  public function getSeconds(): int {
    return $this->seconds;
  }

  /**
   * Returns the "nanoseconds" (ns) part of this time interval (normalized as
   * described, and therefore may differ from the number of nanoseconds
   * specified when this instance was created).
   *
   * Note that nanoseconds are the only unit smaller than a second that we
   * store, so this is the correct method to use when dealing with any other
   * fractional units like milliseconds (ms) and microseconds (us).
   */
  public function getNanoseconds(): int {
    return $this->nanoseconds;
  }

  /**
   * Parts are returned in big-endian order (hours, minutes, seconds,
   * nanoseconds).
   */
  public function getParts(): (int, int, int, int) {
    return
      tuple($this->hours, $this->minutes, $this->seconds, $this->nanoseconds);
  }

  //////////////////////////////////////////////////////////////////////////////
  // checks

  public function isZero(): bool {
    return $this->hours === 0 &&
      $this->minutes === 0 &&
      $this->seconds === 0 &&
      $this->nanoseconds === 0;
  }

  /**
   * Due to normalization, it is guaranteed that a positive time interval will
   * have all of its parts (hours, minutes, seconds, nanoseconds) positive or
   * equal to 0.
   *
   * Note that this method returns false if all parts are equal to 0.
   */
  public function isPositive(): bool {
    return $this->hours > 0 ||
      $this->minutes > 0 ||
      $this->seconds > 0 ||
      $this->nanoseconds > 0;
  }

  /**
   * Due to normalization, it is guaranteed that a negative time interval will
   * have all of its parts (hours, minutes, seconds, nanoseconds) negative or
   * equal to 0.
   *
   * Note that this method returns false if all parts are equal to 0.
   */
  public function isNegative(): bool {
    return $this->hours < 0 ||
      $this->minutes < 0 ||
      $this->seconds < 0 ||
      $this->nanoseconds < 0;
  }

  //////////////////////////////////////////////////////////////////////////////
  // with

  /**
   * Returns a new instance with the "hours" part changed to the specified
   * value. Note that due to normalization, the actual value in the returned
   * instance may differ, and this may affect other parts of the returned
   * instance too, e.g. `Time::hours(2, 30)->withHours(-1)` is equivalent to
   * `Time::hours(-1, 30)` which is normalized to "-30min".
   */
  public function withHours(int $hours): this {
    return self::hours(
      $hours,
      $this->minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  /**
   * Returns a new instance with the "minutes" part changed to the specified
   * value. Note that due to normalization, the actual value in the returned
   * instance may differ, and this may affect other parts of the returned
   * instance too, e.g. `Time::minutes(2, 30)->withMinutes(-1)` is equivalent to
   * `Time::minutes(-1, 30)` which is normalized to "-30sec".
   */
  public function withMinutes(int $minutes): this {
    return self::hours(
      $this->hours,
      $minutes,
      $this->seconds,
      $this->nanoseconds,
    );
  }

  /**
   * Returns a new instance with the "seconds" part changed to the specified
   * value. Note that due to normalization, the actual value in the returned
   * instance may differ, and this may affect other parts of the returned
   * instance too, e.g. `Time::minutes(2, 30)->withSeconds(-30)` is equivalent
   * to `Time::minutes(2, -30)` which is normalized to "1min 30sec".
   */
  public function withSeconds(int $seconds): this {
    return self::hours(
      $this->hours,
      $this->minutes,
      $seconds,
      $this->nanoseconds,
    );
  }

  /**
   * Returns a new instance with the "nanoseconds" part changed to the specified
   * value. Note that due to normalization, the actual value in the returned
   * instance may differ, and this may affect other parts of the returned
   * instance too, e.g. `Time::seconds(2)->withNanoseconds(-1)` is equivalent
   * to `Time::seconds(2, -1)` which is normalized to "1sec 999999999ns".
   */
  public function withNanoseconds(int $nanoseconds): this {
    return self::hours(
      $this->hours,
      $this->minutes,
      $this->seconds,
      $nanoseconds,
    );
  }

  //////////////////////////////////////////////////////////////////////////////
  // comparisons

  /**
   * Returns 1 if this instance represents a time interval longer than $other,
   * -1 if shorter, and 0 if they are equal.
   */
  public function compare(this $other): int {
    if ($this->hours !== $other->hours) {
      return $this->hours <=> $other->hours;
    }
    if ($this->minutes !== $other->minutes) {
      return $this->minutes <=> $other->minutes;
    }
    if ($this->seconds !== $other->seconds) {
      return $this->seconds <=> $other->seconds;
    }
    return $this->nanoseconds <=> $other->nanoseconds;
  }

  public function isEqual(this $other): bool {
    return $this->compare($other) === 0;
  }

  public function isShorter(this $other): bool {
    return $this->compare($other) === -1;
  }

  public function isShorterOrEqual(this $other): bool {
    return $this->compare($other) <= 0;
  }

  public function isLonger(this $other): bool {
    return $this->compare($other) === 1;
  }

  public function isLongerOrEqual(this $other): bool {
    return $this->compare($other) >= 0;
  }

  /**
   * Returns true if this instance represents a time interval longer than $a but
   * shorter than $b, or vice-versa (shorter than $a but longer than $b), or if
   * this instance is equal to $a and/or $b. Returns false if this instance is
   * shorter/longer than both.
   */
  public function isBetweenIncl(this $a, this $b): bool {
    $a = $this->compare($a);
    $b = $this->compare($b);
    return $a === 0 || $a !== $b;
  }

  /**
   * Returns true if this instance represents a time interval longer than $a but
   * shorter than $b, or vice-versa (shorter than $a but longer than $b).
   * Returns false if this instance is equal to $a and/or $b, or shorter/longer
   * than both.
   */
  public function isBetweenExcl(this $a, this $b): bool {
    $a = $this->compare($a);
    $b = $this->compare($b);
    return $a !== 0 && $b !== 0 && $a !== $b;
  }

  //////////////////////////////////////////////////////////////////////////////
  // operations

  /**
   * Returns a new instance, converting a positive/negative interval to the
   * opposite (negative/positive) interval of equal length. The resulting
   * instance has all parts equivalent to the current instance's parts
   * multiplied by -1.
   */
  public function invert(): this {
    if ($this->isZero()) {
      return $this;
    }
    return new self(
      -$this->hours,
      -$this->minutes,
      -$this->seconds,
      -$this->nanoseconds,
    );
  }

  /**
   * Returns a new instance representing the sum of this instance and the
   * provided `$other` instance. Note that time intervals can be negative, so
   * the resulting instance is not guaranteed to be shorter/longer than either
   * of the inputs.
   *
   * This operation is commutative: `$a->plus($b) === $b->plus($a)`
   */
  public function plus(this $other): this {
    if ($other->isZero()) {
      return $this;
    } else if ($this->isZero()) {
      return $other;
    }
    return self::hours(
      $this->hours + $other->hours,
      $this->minutes + $other->minutes,
      $this->seconds + $other->seconds,
      $this->nanoseconds + $other->nanoseconds,
    );
  }

  /**
   * Returns a new instance representing the difference between this instance
   * and the provided `$other` instance (i.e. `$other` subtracted from `$this`).
   * Note that time intervals can be negative, so the resulting instance is not
   * guaranteed to be shorter/longer than either of the inputs.
   *
   * This operation is not commutative: `$a->minus($b) !== $b->minus($a)`
   * But: `$a->minus($b) === $b->minus($a)->invert()`
   */
  public function minus(this $other): this {
    if ($other->isZero()) {
      return $this;
    } else if ($this->isZero()) {
      return $other;
    }
    return self::hours(
      $this->hours - $other->hours,
      $this->minutes - $other->minutes,
      $this->seconds - $other->seconds,
      $this->nanoseconds - $other->nanoseconds,
    );
  }

  //////////////////////////////////////////////////////////////////////////////
  // output

  /**
   * Returns the time interval as string, useful e.g. for debugging. This is not
   * meant to be a comprehensive way to format time intervals for user-facing
   * output.
   */
  public function toString(int $max_decimals = 3): string {
    invariant(
      $max_decimals >= 0,
      'Expected a non-negative number of decimals.',
    );
    $decimal_part = '';
    if ($max_decimals > 0) {
      $decimal_part = (string)Math\abs($this->nanoseconds)
        |> Str\pad_left($$, 9, '0')
        |> Str\slice($$, 0, $max_decimals)
        |> Str\trim_right($$, '0');
    }
    if ($decimal_part !== '') {
      $decimal_part = '.'.$decimal_part;
    }

    $sec_sign = $this->seconds < 0 || $this->nanoseconds < 0 ? '-' : '';
    $sec = Math\abs($this->seconds);

    $values = vec[
      tuple((string)$this->hours, 'hr'),
      tuple((string)$this->minutes, 'min'),
      tuple($sec_sign.$sec.$decimal_part, 'sec'),
    ];
    for (
      $end = C\count($values);
      $end > 0 && $values[$end - 1][0] === '0';
      --$end
    ) {}
    for (
      $start = 0;
      $start < $end && $values[$start][0] === '0';
      ++$start
    ) {}
    $output = vec[];
    for ($i = $start; $i < $end; ++$i) {
      $output[] = $values[$i][0].$values[$i][1];
    }
    return C\is_empty($output) ? '0sec' : Str\join($output, ' ');
  }
}
