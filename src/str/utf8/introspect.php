<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Str\Utf8;

use namespace HH\Lib\_Private;
use type HH\Lib\Experimental\Str\Encoding;

/**
 * Returns the length of the given string.
 * A multi-byte character is counted as 1.
 *
 * Previously known in PHP as `mb_strlen`.
 */
<<__RxLocal>>
function length(string $string): int {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_strlen($string, Encoding::UTF8);
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
 * - To get the case-insensitive position, see `Str\Utf8\search_ci()`.
 * - To get the last position of the needle, see `Str\Utf8\search_last()`.
 *
 * Previously known in PHP as `mb_strpos`.
 */
<<__RxLocal>>
function search(string $haystack, string $needle, int $offset = 0): ?int {
  $offset = _Private\validate_offset($offset, length($haystack));
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $position = \mb_strpos($haystack, $needle, $offset, Encoding::UTF8);
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
 * - To get the case-sensitive position, see `Str\Utf8\search()`.
 * - To get the last position of the needle, see `Str\Utf8\search_last()`.
 *
 * Previously known in PHP as `mb_stripos`.
 */
<<__RxLocal>>
function search_ci(string $haystack, string $needle, int $offset = 0): ?int {
  $offset = _Private\validate_offset($offset, length($haystack));
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $position = \mb_stripos($haystack, $needle, $offset, Encoding::UTF8);
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
 * - To get the first position of the needle, see `Str\Utf8\search()`.
 *
 * Previously known in PHP as `mb_strrpos`.
 */
<<__RxLocal>>
function search_last(string $haystack, string $needle, int $offset = 0): ?int {
  $haystack_length = length($haystack);
  invariant(
    $offset >= -$haystack_length && $offset <= $haystack_length,
    'Offset is out-of-bounds.',
  );
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $position = \mb_strrpos($haystack, $needle, $offset, Encoding::UTF8);
  if ($position === false) {
    return null;
  }
  return $position;
}

/**
 * Determine whether a string of unknown encoding is valid UTF-8
 */
<<__RxLocal>>
function is_utf8(string $string): bool {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_check_encoding($string, Encoding::UTF8);
}
