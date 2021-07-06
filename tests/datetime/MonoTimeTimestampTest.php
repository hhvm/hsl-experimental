<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\MonoTime;

use function Facebook\FBExpect\expect;

final class MonoTimeTimestampTest extends TimestampTestBase {

  const type TTimestamp = MonoTime\Timestamp;

  protected static function fromRaw(
    int $seconds,
    int $nanoseconds = 0,
  ): MonoTime\Timestamp {
    return MonoTime\Timestamp::fromRaw($seconds, $nanoseconds);
  }

  <<__Override>>
  protected static function asComparable(
    MonoTime\Timestamp $timestamp,
  ): MonoTime\Timestamp {
    return $timestamp;
  }

  public function testNow(): void {
    $a = MonoTime\Timestamp::now();
    $b = MonoTime\Timestamp::now();
    expect($a->isBeforeOrAtTheSameTime($b))->toBeTrue();
  }
}
