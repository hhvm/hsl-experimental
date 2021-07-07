<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\DateTime;

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\{DataProvider, HackTest};

final class ZonedDateTimeTest extends DateTimeTestBase {

  const type TDateTime = DateTime\Zoned;

  <<__Override>>
  protected static function fromParts(
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): DateTime\Builder<DateTime\Zoned> {
    return DateTime\Zoned::fromParts(
      DateTime\Zone::UTC,
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
  protected static function asComparable(DateTime\Zoned $dt): DateTime\Zoned {
    return $dt;
  }
}
