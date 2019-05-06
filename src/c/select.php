<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\C;

use namespace HH\Lib\{_Private, Str};

/**
 * Returns the key of the first value of the given KeyedTraversable for which
 * the predicate returns true, or null if no such value is found.
 *
 * Time complexity: O(n)
 * Space complexity: O(1)
 */
<<__Rx, __AtMostRxAsArgs>>
function find_key_with_key<Tk, Tv>(
  <<
    __MaybeMutable,
    __OnlyRxIfImpl(\HH\Rx\KeyedTraversable::class)
  >> KeyedTraversable<Tk, Tv> $traversable,
  <<__AtMostRxAsFunc>> (function(Tk, Tv): bool) $key_value_predicate,
): ?Tk {
  foreach ($traversable as $key => $value) {
    if ($key_value_predicate($key, $value)) {
      return $key;
    }
  }
  return null;
}
