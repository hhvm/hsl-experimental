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

final class DateTimeTimestampTest extends TimestampTestBase {

  const type TTimestamp = DateTime\Timestamp;

  <<__Override>>
  protected static function fromRaw<T>(
    int $seconds,
    int $nanoseconds = 0,
  ): DateTime\Timestamp {
    return DateTime\Timestamp::fromRaw($seconds, $nanoseconds);
  }

  <<__Override>>
  protected static function asComparable(
    DateTime\Timestamp $timestamp,
  ): DateTime\Timestamp {
    return $timestamp;
  }

  public function testConversions(): void {
    $ts = DateTime\Timestamp::now();
    expect($ts->getTimestamp())->toEqual($ts);

    $ts = DateTime\Timestamp::fromRaw(1234567890, 42);
    expect($ts->convertToTimezone(DateTime\Zone::UTC)->getParts())
      ->toEqual(tuple(2009, 2, 13, 23, 31, 30, 42));
    expect($ts->convertToTimezone(DateTime\Zone::EUROPE_PRAGUE)->getParts())
      ->toEqual(tuple(2009, 2, 14, 0, 31, 30, 42));
  }

  public function testFormat(): void {
    // $timestamp->format() just delegates to strftime() so we don't need to
    // thoroughly test it here, we only do some basic sanity checks.
    expect(
      DateTime\Timestamp::fromRaw(1234567890)
        ->format(DateTime\Zone::UTC, '%Y-%m-%d %H:%M:%S'),
    )->toEqual('2009-02-13 23:31:30');
    expect(
      DateTime\Timestamp::fromRaw(1234567890)
        ->format(DateTime\Zone::EUROPE_PRAGUE, '%Y-%m-%d %H:%M:%S %Z'),
    )->toEqual('2009-02-14 00:31:30 CET');
  }

  public function testParse(): void {
    // Timestamp::parse() just delegates to strtotime() so we don't need to
    // thoroughly test it here, we only do some basic sanity checks.
    expect(
      DateTime\Timestamp::parse(DateTime\Zone::UTC, '2009-02-13 23:31:30')
        ->toRaw()
    )->toEqual(tuple(1234567890, 0));

    expect(
      DateTime\Timestamp::parse(
        DateTime\Zone::EUROPE_PRAGUE,
        '-2 days',
        DateTime\Timestamp::fromRaw(1234567890),
      )->toRaw()
    )->toEqual(tuple(1234567890 - 2 * 86400, 0));
  }
}
