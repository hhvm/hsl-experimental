<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Dict;

/**
 * Applies a function to each value in a KeyedTraversable.
 * Returns a dict with the same keys and unaltered values
 * as the passed KeyedTraversable<Tk, Tv>.
 *
 * For returning a new dict with the return values of $value_func 
 * see `Dict\map`.
 *
 * Time complexity: O(n)
 * Space complexity: O(n)
 */
<<__Rx, __AtMostRxAsArgs>>
function for_each<Tk as arraykey, Tv>(
  <<
    __MaybeMutable,
    __OnlyRxIfImpl(\HH\Rx\Traversable::class)
  >> KeyedTraversable<Tk, Tv> $traversable,
  <<__AtMostRxAsFunc>> (function(Tv): void) $value_func,
): dict<Tk, Tv> {
  $dict = dict($traversable);
  foreach ($dict as $value) {
    $value_func($value);
  }
  return $dict;
}

/**
 * Applies a function to each value in a KeyedTraversable.
 * Returns a dict with the same unaltered keys and values
 * as the passed KeyedTraversable<Tk, Tv>.
 *
 * For returning a new dict with the return values of $value_func
 * see `Dict\map_with_key`.
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
): dict<Tk, Tv> {
  $dict = dict($traversable);
  foreach ($dict as $key => $value) {
    $value_func($key, $value);
  }
  return $dict;
}
