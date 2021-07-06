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
 * Implementation of DateTime\Builder<DateTime\Unzoned>. This class is an
 * implementation detail and shouldn't be referenced directly.
 */
final class UnzonedBuilder extends DateTime\Builder<DateTime\Unzoned> {

  public function __construct(
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ) {
    $this->timezone = DateTime\Zone::UTC;
    $this->year = $year;
    $this->month = $month;
    $this->day = $day;
    $this->hours = $hours;
    $this->minutes = $minutes;
    $this->seconds = $seconds;
    $this->nanoseconds = $nanoseconds;
  }

  <<__Override>>
  protected static function builderFromParts(
    DateTime\Zone $_,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): this {
    return new self(
      $year,
      $month,
      $day,
      $hours,
      $minutes,
      $seconds,
      $nanoseconds,
    );
  }

  <<__Override>>
  protected static function instanceFromPartsX(
    DateTime\Zone $_,
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): DateTime\Unzoned {
    return DateTime\Unzoned::fromPartsX(
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
