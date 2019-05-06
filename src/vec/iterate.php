<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Vec;

/**
 * Applies a function to each value in a Traversable.
 * Returns a vec with the same unaltered values as the passed Traversable<Tv>.
 *
 * For returning a new vec with the return values of $value_func see `Vec\map`.
 *
 * Time complexity: O(n)
 * Space complexity: O(n)
 */
<<__Rx, __AtMostRxAsArgs>>
function for_each<Tv>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>> Traversable<Tv>
    $traversable,
  <<__AtMostRxAsFunc>> (function(Tv): void) $value_func,
): vec<Tv> {
  $result = vec[];
  $vec = vec($traversable);
  foreach ($vec as $value) {
    $value_func($value);
  }
  return $result;
}

/**
 * Applies a function to each key and value in a Traversable.
 * Returns a vec with the same unaltered values as the passed Traversable<Tv>.
 *
 * For returning a new vec with the return values of $value_func
 * see `Vec\map_with_key`.
 *
 * Time complexity: O(n)
 * Space complexity: O(n)
 */
<<__Rx, __AtMostRxAsArgs>>
function for_each_with_key<Tk as arraykey, Tv>(
  <<
    __MaybeMutable,
    __OnlyRxIfImpl(\HH\Rx\KeyedTraversable::class)
  >> KeyedTraversable<Tk, Tv> $traversable,
  <<__AtMostRxAsFunc>> (function(Tk, Tv): void) $value_func,
): vec<Tv> {
  $dict = dict($traversable);
  $result = vec[];
  foreach ($dict as $key => $value) {
    $value_func($key, $value);
  }
  return $result;
}
