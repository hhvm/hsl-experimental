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
 * Shared logic for `DateTime\Timestamp` and `MonoTime\Timestamp`.
 */
<<__ConsistentConstruct>>
abstract class Timestamp extends Comparable {

  private function __construct(
    private int $seconds,
    private int $nanoseconds,
  ) {}

  /**
   * Returns an instance representing the specified number of `$seconds` since
   * the epoch (00:00:00 UTC on 1 January 1970 for `DateTime\Timestamp`;
   * arbitrary for `MonoTime\Timestamp`), adjusted by the specified number of
   * `$nanoseconds`.
   *
   * The resulting instance is normalized such that the number of nanoseconds is
   * always between 0 and 999999999, for example:
   * - `Timestamp::fromRaw(42, -100)` normalizes to (41, 999999900)
   * - `Timestamp::fromRaw(-42, -100)` normalizes to (-43, 999999900)
   * - `Timestamp::fromRaw(42, 1000000100)` normalizes to (43, 100)
   */
  final public static function fromRaw(
    int $seconds,
    int $nanoseconds = 0,
  ): this {
    $seconds += (int)($nanoseconds / NS_IN_SEC);
    $nanoseconds %= NS_IN_SEC;
    if ($nanoseconds < 0) {
      --$seconds;
      $nanoseconds += NS_IN_SEC;
    }
    return new static($seconds, $nanoseconds);
  }

  /**
   * Returns `tuple($this->getSeconds(), $this->getNanoseconds())`.
   */
  final public function toRaw(): (int, int) {
    return tuple($this->seconds, $this->nanoseconds);
  }

  /**
   * Returns this timestamp as number of seconds since the epoch (00:00:00 UTC
   * on 1 January 1970 for `DateTime\Timestamp`; arbitrary for
   * `MonoTime\Timestamp`). Can be negative, if this timestamp represents a
   * point in time before the epoch.
   */
  final public function getSeconds(): int {
    return $this->seconds;
  }

  /**
   * Returns the nanosecond part of this `Timestamp`. Guaranteed to be between
   * 0 and 999999999 (inclusive).
   */
  final public function getNanoseconds(): int {
    return $this->nanoseconds;
  }

  <<__Override>>
  final public function plus(Time $time): this {
    list($h, $m, $s, $ns) = $time->getParts();
    $s += 60 * $m + 3600 * $h;
    return static::fromRaw($this->seconds + $s, $this->nanoseconds + $ns);
  }

  <<__Override>>
  final public function minus(Time $time): this {
    list($h, $m, $s, $ns) = $time->getParts();
    $s += 60 * $m + 3600 * $h;
    return static::fromRaw($this->seconds - $s, $this->nanoseconds - $ns);
  }
}
