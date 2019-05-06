<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Keyset;

/**
 * Applies a function to each value in a Traversable.
 * Returns a keyset with the same unaltered values
 * as the passed Traversable<Tv>.
 *
 * For returning a new keyset with the return values of $value_func 
 * see `Keyset\map`.
 *
 * Time complexity: O(n)
 * Space complexity: O(n)
 */
<<__Rx, __AtMostRxAsArgs>>
function for_each<Tv as arraykey>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>> Traversable<Tv>
    $traversable,
  <<__AtMostRxAsFunc>> (function(Tv): void) $value_func,
): keyset<Tv> {
  $result = keyset[];
  foreach ($traversable as $value) {
    $result[] = $value;
    $value_func($value);
  }
  return $result;
}

/**
 * Applies a function to each key and value in a KeyedTraversable.
 * Returns a keyset with the same unaltered values
 * as the passed Traversable<Tv>.
 *
 * For returning a new vec with the return values of $value_func
 * see `Keyset\map_with_key`.
 *
 * Time complexity: O(n)
 * Space complexity: O(n)
 */
<<__Rx, __AtMostRxAsArgs>>
function for_each_with_key<Tk, Tv as arraykey>(
  <<
    __MaybeMutable,
    __OnlyRxIfImpl(\HH\Rx\KeyedTraversable::class)
  >> KeyedTraversable<Tk, Tv> $traversable,
  <<__AtMostRxAsFunc>> (function(Tk, Tv): void) $value_func,
): keyset<Tv> {
  $result = keyset[];
  foreach ($traversable as $key => $value) {
    $result[] = $value;
    $value_func($key, $value);
  }
  return $result;
}
