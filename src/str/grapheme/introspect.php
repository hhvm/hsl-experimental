<?hh // strict
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

/**
 * Returns the length of the given string in grapheme units.
 *
 * Previously known in PHP as `grapheme_strlen`.
 */
<<__RxLocal>>
function length(string $string): int {
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
 * - To get the last position of the needle, see `Str\Grapheme\search_last()`.
 *
 * Previously known in PHP as `grapheme_strpos`.
 */
<<__RxLocal>>
function search(string $haystack, string $needle, int $offset = 0): ?int {
  $offset = _Private\validate_offset($offset, length($haystack));
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
 * - To get the last position of the needle, see `Str\Grapheme\search_last()`.
 *
 * Previously known in PHP as `grapheme_stripos`.
 */
<<__RxLocal>>
function search_ci(string $haystack, string $needle, int $offset = 0): ?int {
  $offset = _Private\validate_offset($offset, length($haystack));
  $position = \grapheme_stripos($haystack, $needle, $offset);
  if ($position === false) {
    return null;
  }
  return $position;
}

/**
 * Returns the last position of the "needle" string in the "haystack" string,
 * or null if it isn't found.
 *
 * An optional offset determines where in the haystack (from the beginning) the
 * search begins. If the offset is negative, the search will begin that many
 * characters from the end of the string and go backwards. If the offset is
 * out-of-bounds, a ViolationException will be thrown.
 *
 * - To simply check if the haystack contains the needle, see `Str\contains()`.
 * - To get the first position of the needle, see `Str\Grapheme\search()`.
 *
 * Previously known in PHP as `grapheme_strrpos`.
 */
<<__RxLocal>>
function search_last(string $haystack, string $needle, int $offset = 0): ?int {
  $haystack_length = length($haystack);
  invariant(
    $offset >= -$haystack_length && $offset <= $haystack_length,
    'Offset is out-of-bounds.',
  );
  $position = \grapheme_strrpos($haystack, $needle, $offset);
  if ($position === false) {
    return null;
  }
  return $position;
}