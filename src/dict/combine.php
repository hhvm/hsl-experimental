<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */
namespace HH\Eexperimental\Lib\Dict;

use namespace HH\Lib\C;

/*
 * Merge multiple KeyedTraversables into a new dict. In the case of duplicate
 * keys, the value will be ignored.
 */
function union<Tk as arraykey, Tv>(
  KeyedTraversable<Tk, Tv> ...$traversables
): dict<Tk, Tv> {
  $result = dict[];
  foreach ($traversables as $traversable) {
    foreach ($traversable as $key => $value) {
        if (!C\contains_key($result, $key)) {
            $result[$key] = $value;
        }
    }
  }
  return $result;
}
