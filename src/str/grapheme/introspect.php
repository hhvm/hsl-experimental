<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Str\Grapheme;

use namespace HH\Lib\_Private;
use namespace HH\Lib\Str;

/**
 * Returns the length of the given string in grapheme units.
 *
 * Previously known in PHP as `grapheme_strlen`.
 */
<<__RxLocal>>
function length(string $string): int {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \grapheme_strlen($string);
}

/**
 * Returns the first position of the "needle" string in the "haystack" string,
 * or null if it isn't found.
 *
 * An optional offset determines where in the haystack the search begins. If the
 * offset is negative, the search will begin that many characters from the end
 * of the string. If the offset is out-of-bounds, a ViolationException will be
 * thrown.
 *
 * - To simply check if the haystack contains the needle, see `Str\contains()`.
 * - To get the case-insensitive position, see `Str\Grapheme\search_ci()`.
 *
 * Previously known in PHP as `grapheme_strpos`.
 */
<<__RxLocal>>
function search(string $haystack, string $needle, int $offset = 0): ?int {
  $offset = _Private\validate_offset($offset, length($haystack));
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $position = \grapheme_strpos($haystack, $needle, $offset);
  if ($position === false) {
    return null;
  }
  return $position;
}

/**
 * Returns the first position of the "needle" string in the "haystack" string,
 * or null if it isn't found (case-insensitive).
 *
 * An optional offset determines where in the haystack the search begins. If the
 * offset is negative, the search will begin that many characters from the end
 * of the string. If the offset is out-of-bounds, a ViolationException will be
 * thrown.
 *
 * - To simply check if the haystack contains the needle, see `Str\contains_ci()`.
 * - To get the case-sensitive position, see `Str\Grapheme\search()`.
 *
 * Previously known in PHP as `grapheme_stripos`.
 */
<<__RxLocal>>
function search_ci(string $haystack, string $needle, int $offset = 0): ?int {
  $offset = _Private\validate_offset($offset, length($haystack));
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $position = \grapheme_stripos($haystack, $needle, $offset);
  if ($position === false) {
    return null;
  }
  return $position;
}
