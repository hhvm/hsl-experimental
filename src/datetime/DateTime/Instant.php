<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\DateTime;
use namespace HH\Lib\Experimental\_Private\_DateTime;

/**
 * An absolute, unambiguous point in time. Implemented by `DateTime\Zoned` and
 * `DateTime\Timestamp`, but not `DateTime\Unzoned`.
 */
interface Instant {
  require extends _DateTime\Comparable;
  const type TComparableTo = Instant;

  /**
   * Coverts this `Instant` to the equivalent `Timestamp`. If it is already a
   * `Timestamp`, returns it unchanged.
   */
  public function getTimestamp(): Timestamp;

  /* TODO
  public function convertToTimezone(Zone $timezone): Zoned;
  */
}
