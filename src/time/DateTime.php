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
final class DateTime extends DateTimeBase {

  /**
   * Returns a new DateTime that represents the instant represented by the
   * given local time in the given Timezone.
   */
  public static function fromValues(
    Timezone $timezone,
    int $year = 1970,
    int $month = 1,
    int $day = 1,
    int $hour = 0,
    int $minute = 0,
    int $second = 0,
    int $millisecond = 0,
    int $microsecond = 0,
    int $nanosecond = 0,
  ): this {
    return static::__createFromValues(
      $timezone,
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
   * Returns a new DateTime that represents given instant viewed from the given
   * Timezone.
   */
  public static function fromTimestamp(
    Timezone $timezone,
    Timestamp<Unix> $timestamp,
  ): this {
    return static::__createFromTimestamp(
      $timezone,
      timestamp_to_ns($timestamp),
    );
  }

  /**
   * Returns this DateTime's Unix timestamp.
   */
  public function getTimestamp(): Timestamp<Unix> {
    return timestamp_from_ns($this->unixTimestamp);
  }

  /**
   * Returns this DateTime's Timezone.
   */
  public function getTimezone(): Timezone {
    return $this->timezone;
  }

  /**
   * Returns a new DateTime that represents this DateTime's instant viewed from
   * the given Timezone.
   */
  public function withTimezone(Timezone $timezone): this {
    return static::__createFromTimestamp($timezone, $this->unixTimestamp);
  }

  /**
   * Returns a new DateTime that represents the given instant viewed from
   * this DateTime's Timezone.
   */
  public function withTimestamp(Timestamp<Unix> $timestamp): this {
    return static::fromTimestamp($this->timezone, $timestamp);
  }

  /**
   * Returns whether this DateTime's instant is before the given DateTime.
   */
  final public function isBefore(this $datetime): bool {
    return $this->unixTimestamp < $datetime->unixTimestamp;
  }

  /**
   * Returns whether this DateTime's instant is after the given DateTime.
   */
  final public function isAfter(this $datetime): bool {
    return $this->unixTimestamp > $datetime->unixTimestamp;
  }

  /**
   * Returns whether this DateTime's instant is the same as the given DateTime.
   */
  final public function isEqualTo(this $datetime): bool {
    return $this->unixTimestamp === $datetime->unixTimestamp;
  }

  /**
   * Returns whether this DateTime's instant is before the given Unix timestamp
   * in nanoseconds.
   */
  public function isBeforeTimestamp(Timestamp<Unix> $timestamp): bool {
    return $this->unixTimestamp < timestamp_to_ns($timestamp);
  }

  /**
   * Returns whether this DateTime's instant is after the given Unix timestamp
   * in nanoseconds.
   */
  public function isAfterTimestamp(Timestamp<Unix> $timestamp): bool {
    return $this->unixTimestamp > timestamp_to_ns($timestamp);
  }

  /**
   * Returns whether this DateTime's instant is the same as the given Unix
   * timestamp in nanoseconds.
   */
  public function isEqualToTimestamp(Timestamp<Unix> $timestamp): bool {
    return $this->unixTimestamp === timestamp_to_ns($timestamp);
  }

}
