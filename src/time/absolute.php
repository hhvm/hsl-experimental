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

type Nanoseconds = int;

newtype Clock = int;
newtype Mono as Clock = Clock;
newtype Unix as Clock = Clock;
newtype Timestamp<Tc> = Nanoseconds;

/**
 * Returns a Timestamp representing the given number of nanoseconds elapsed
 * since 00:00:00 1 January 1970 (UTC).
 */
function timestamp_from_ns(
  int $nanoseconds,
): Timestamp<Unix> {
  return $nanoseconds;
}

/**
 * Returns an integer representing the number of nanoseconds in the given
 * Timestamp.
 */
function timestamp_to_ns<Tc as Clock>(
  Timestamp<Tc> $timestamp,
): int {
  return $timestamp;
}
