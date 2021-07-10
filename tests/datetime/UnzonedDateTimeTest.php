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

final class UnzonedDateTimeTest extends DateTimeTestBase {

  const type TDateTime = DateTime\Unzoned;

  <<__Override>>
  protected static function fromParts(
    int $year,
    int $month,
    int $day,
    int $hours,
    int $minutes,
    int $seconds,
    int $nanoseconds,
  ): DateTime\Builder<DateTime\Unzoned> {
    return DateTime\Unzoned::fromParts(
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
  protected static function asComparable(
    DateTime\Unzoned $dt,
  ): DateTime\Unzoned {
    return $dt;
  }

  public function testConversions(): void {
    $unzoned = DateTime\Unzoned::fromPartsX(2009, 2, 13, 23, 31, 30, 42);

    $utc = expect($unzoned->withTimezone(DateTime\Zone::UTC)->exactX())
      ->toBeInstanceOf(DateTime\Zoned::class);
    expect($utc->getParts())->toEqual(tuple(2009, 2, 13, 23, 31, 30, 42));
    expect($utc->getTimestamp()->toRaw())->toEqual(tuple(1234567890, 42));

    $cet =
      expect($unzoned->withTimezone(DateTime\Zone::EUROPE_PRAGUE)->exactX())
        ->toBeInstanceOf(DateTime\Zoned::class);
    expect($cet->getParts())->toEqual(tuple(2009, 2, 13, 23, 31, 30, 42));
    expect($cet->getTimestamp()->toRaw())
      ->toEqual(tuple(1234567890 - 3600, 42));
  }

  public function testFormat(): void {
    // $datetime->format() just delegates to strftime() so we don't need to
    // thoroughly test it here, we only do some basic sanity checks.
    expect(
      DateTime\Unzoned::fromPartsX(2021, 2, 3, 4, 5, 6, 7)
        ->format('%Y-%m-%d %H:%M:%S'),
    )->toEqual('2021-02-03 04:05:06');
  }

  public function testParse(): void {
    // Unzoned::parse() just delegates to strtotime() so we don't need to
    // thoroughly test it here, we only do some basic sanity checks.
    expect(
      DateTime\Unzoned::parse('2009-02-13 23:31:30')->getParts()
    )->toEqual(tuple(2009, 2, 13, 23, 31, 30, 0));

    expect(
      DateTime\Unzoned::parse(
        '-2 days',
        DateTime\Unzoned::fromPartsX(2021, 2, 3, 4, 5, 6, 7),
      )->getParts()
    )->toEqual(tuple(2021, 2, 1, 4, 5, 6, 0));
  }
}
