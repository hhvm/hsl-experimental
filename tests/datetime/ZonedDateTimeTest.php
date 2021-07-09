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
use type HH\Lib\Experimental\Time;

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

  <<__Override>>
  protected static function provideCompareSubclass(
  ): vec<(DateTime\Zoned, DateTime\Zoned, int)> {
    // additional comparison test cases for comparisons across timezones
    $utc = DateTime\Zone::UTC;
    $cet = DateTime\Zone::EUROPE_PRAGUE;
    return vec[
      // same date/time parts, different timezone
      tuple(
        DateTime\Zoned::fromPartsX($utc, 2021, 2, 3, 4, 5, 6, 7),
        DateTime\Zoned::fromPartsX($cet, 2021, 2, 3, 4, 5, 6, 7),
        1,
      ),
      // different parts, but same point in time (due to different timezones)
      tuple(
        DateTime\Zoned::fromPartsX($utc, 2021, 2, 3, 4, 5, 6, 7),
        DateTime\Zoned::fromPartsX($cet, 2021, 2, 3, 5, 5, 6, 7),
        0,
      ),
      // 4:10 is actually before 4:05 (due to different timezones)
      tuple(
        DateTime\Zoned::fromPartsX($cet, 2021, 2, 3, 4, 10, 6, 7),
        DateTime\Zoned::fromPartsX($utc, 2021, 2, 3, 4, 5, 6, 7),
        -1,
      ),
    ];
  }

  public function testFactoryMethods(): void {
    expect(DateTime\Zoned::now(DateTime\Zone::EUROPE_PRAGUE)->getTimezone())
      ->toEqual(DateTime\Zone::EUROPE_PRAGUE);

    expect(
      DateTime\Zoned::todayAt(DateTime\Zone::EUROPE_PRAGUE, 16, 42)->exactX()
        ->getTime(),
    )->toEqual(tuple(16, 42, 0, 0));

    $ts = DateTime\Timestamp::fromRaw(1234567890, 42);
    expect(DateTime\Zoned::fromTimestamp(DateTime\Zone::UTC, $ts)->getParts())
      ->toEqual(tuple(2009, 2, 13, 23, 31, 30, 42));
    expect(
      DateTime\Zoned::fromTimestamp(DateTime\Zone::EUROPE_PRAGUE, $ts)
        ->getParts(),
    )->toEqual(tuple(2009, 2, 14, 0, 31, 30, 42));
  }

  public static function provideTimezoneMetadata(
  ): vec<(DateTime\Zone, int, Time, bool)> {
    $marquesas = DateTime\Zone::PACIFIC_MARQUESAS;
    return vec[
      // timezone, month, offset hours, offset minutes, is DST
      tuple(DateTime\Zone::UTC, 1, Time::zero(), false),
      tuple(DateTime\Zone::UTC, 7, Time::zero(), false),
      tuple(DateTime\Zone::EUROPE_PRAGUE, 1, Time::hours(1), false),
      tuple(DateTime\Zone::EUROPE_PRAGUE, 7, Time::hours(2), true),
      tuple(DateTime\Zone::PACIFIC_CHATHAM, 1, Time::fromParts(13, 45), true),
      tuple(DateTime\Zone::PACIFIC_CHATHAM, 7, Time::fromParts(12, 45), false),
      tuple($marquesas, 1, Time::fromParts(-9, -30), false),
      tuple($marquesas, 7, Time::fromParts(-9, -30), false),
    ];
  }

  <<DataProvider('provideTimezoneMetadata')>>
  public function testTimezoneMetadata(
    DateTime\Zone $timezone,
    int $month,
    Time $offset,
    bool $is_dst,
  ): void {
    $dt = DateTime\Zoned::fromPartsX($timezone, 2021, $month, 1);
    expect($dt->getTimezone())->toEqual($timezone);
    expect($dt->getTimezoneOffset()->isEqual($offset))->toBeTrue();
    expect($dt->isDST())->toEqual($is_dst);
  }

  public function testTimezoneConversions(): void {
    $prague = DateTime\Zoned::fromPartsX(
      DateTime\Zone::EUROPE_PRAGUE,
      2021,
      2,
      3,
      4,
      5,
      6,
      7,
    );

    // unzoned
    $unzoned = expect($prague->withoutTimezone())
      ->toBeInstanceOf(DateTime\Unzoned::class);
    expect($unzoned->getParts())->toEqual(tuple(2021, 2, 3, 4, 5, 6, 7));

    // same timestamp, different parts
    $riga = expect($prague->convertToTimezone(DateTime\Zone::EUROPE_RIGA))
      ->toBeInstanceOf(DateTime\Zoned::class);
    expect($riga->isAtTheSameTime($prague))->toBeTrue();
    expect($riga->isEqualIncludingTimezone($prague))->toBeFalse();
    expect($riga->getTimestamp()->toRaw())->toEqual(
      $prague->getTimestamp()->toRaw(),
    );
    expect($riga->getParts())->toEqual(tuple(2021, 2, 3, 5, 5, 6, 7));

    // same parts, different timestamp
    $adjusted = $prague->withoutTimezone()
      ->withTimezone(DateTime\Zone::EUROPE_RIGA)
      ->exactX();
    expect($adjusted->isAtTheSameTime($prague))->toBeFalse();
    expect($adjusted->isEqualIncludingTimezone($prague))->toBeFalse();
    expect($adjusted->getParts())->toEqual(tuple(2021, 2, 3, 4, 5, 6, 7));
    expect($adjusted->getTimestamp()->toRaw())
      ->toEqual($prague->getTimestamp()->minusHours(1)->toRaw());

    // equal including timezone
    $equal = DateTime\Zoned::fromPartsX(
      DateTime\Zone::EUROPE_PRAGUE,
      2021,
      2,
      3,
      4,
      5,
      6,
      7,
    );
    expect($equal)->toNotEqual($prague); // not the same object
    expect($equal->isEqualIncludingTimezone($prague))->toBeTrue();
  }

  public function testDSTTransitions(): void {
    $tz = DateTime\Zone::AMERICA_LOS_ANGELES;

    // Invalid time due to DST change.
    $builder = DateTime\Zoned::fromParts($tz, 2019, 3, 10, 2, 30);
    expect($builder->isValid())->toBeFalse();
    expect($builder->closest()->getParts())
      ->toEqual(tuple(2019, 3, 10, 3, 30, 0, 0));

    // Ambiguous time due to DST change.
    $dt1 = DateTime\Zoned::fromParts($tz, 2019, 11, 3, 1, 30)->exactX();
    expect($dt1->getTimestamp()->toRaw())->toEqual(tuple(1572769800, 0));
    expect($dt1->isDST())->toBeTrue();
    expect($dt1->getTimezoneOffset()->isEqual(Time::hours(-7)))->toBeTrue();

    $dt2 = $dt1->plusHours(1); // should have all parts equal, different offset
    expect($dt2->getTimestamp()->toRaw())->toEqual(tuple(1572769800 + 3600, 0));
    expect($dt2->getParts())->toEqual($dt1->getParts());
    expect($dt2->isDST())->toBeFalse();
    expect($dt2->getTimezoneOffset()->isEqual(Time::hours(-8)))->toBeTrue();

    // Test other ways to arrive at an invalid/ambiguous datetime.
    $builder = DateTime\Zoned::fromPartsX($tz, 2019, 3, 9, 2, 30)
      ->plusDays(1);
    expect($builder->isValid())->toBeFalse();
    expect($builder->closest()->getParts())
      ->toEqual(tuple(2019, 3, 10, 3, 30, 0, 0));

    $builder = DateTime\Zoned::fromPartsX($tz, 2020, 12, 10, 2, 30)
      ->minusYears(1)
      ->withMonth(3);
    expect($builder->isValid())->toBeFalse();
    expect($builder->closest()->getParts())
      ->toEqual(tuple(2019, 3, 10, 3, 30, 0, 0));

    $tomorrow = DateTime\Zoned::fromPartsX($tz, 2019, 11, 4, 1, 30);
    $yesterday = $tomorrow->minusDays(1)->exactX();
    expect($yesterday->getParts())->toEqual(tuple(2019, 11, 3, 1, 30, 0, 0));
    // Ambiguous time resolves to the earlier one, which is actually 25 hours
    // before the same time tomorrow.
    expect($tomorrow->timeSince($yesterday)->getHours())->toEqual(25);
  }

  public function testFormat(): void {
    // $datetime->format() just delegates to strftime() so we don't need to
    // thoroughly test it here, we only do some basic sanity checks.
    $tz = DateTime\Zone::EUROPE_PRAGUE;
    expect(
      DateTime\Zoned::fromPartsX($tz, 2021, 2, 3, 4, 5, 6, 7)
        ->format('%Y-%m-%d %H:%M:%S %Z'),
    )->toEqual('2021-02-03 04:05:06 CET');
  }

  public function testParse(): void {
    // Zoned::parse() just delegates to strtotime() so we don't need to
    // thoroughly test it here, we only do some basic sanity checks.
    $prague = DateTime\Zone::EUROPE_PRAGUE;
    $riga = DateTime\Zone::EUROPE_RIGA;

    $dt = DateTime\Zoned::parse($prague, '2009-02-13 23:31:30');
    expect($dt->getParts())->toEqual(tuple(2009, 2, 13, 23, 31, 30, 0));
    expect($dt->getTimestamp()->toRaw())->toEqual(tuple(1234567890 - 3600, 0));

    expect(
      DateTime\Zoned::parse(
        $prague,
        '-2 days',
        DateTime\Zoned::fromPartsX($riga, 2021, 2, 3, 4, 5, 6, 7),
      )->getParts(),
    )->toEqual(tuple(2021, 2, 1, 3, 5, 6, 0));
  }
}
