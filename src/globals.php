<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

/**
 * Returns whether the input is a Hack array.
 */
<<__Rx>>
function is_hack_array(mixed $val): bool {
  return is_dict($val) || is_vec($val) || is_keyset($val);
}

/**
 * Returns whether the input is either a PHP array or Hack array.
 */
<<__Rx>>
function is_any_array(mixed $val): bool {
  return is_array($val) || is_hack_array($val);
}
