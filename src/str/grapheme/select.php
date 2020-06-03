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

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned. If the
 * offset is out-of-bounds, a ViolationException will be thrown.
 *
 * Previously known in PHP as `grapheme_substr`.
 */
<<__RxLocal>>
function slice(string $string, int $offset, ?int $length = null): string {
  invariant($length === null || $length >= 0, 'Expected non-negative length.');
  $offset = _Private\validate_offset($offset, length($string));
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $result = \grapheme_substr($string, $offset, $length);
  if ($result === false) {
    return '';
  }
  return $result;
}

/**
 * Function to extract a sequence of default grapheme clusters from a text buffer,
 * which must be encoded in UTF-8.
 *
 * `$size` determines the number of graphemes to extract, starting at the
 * `$start` position (in bytes).
 *
 * Returns a tuple containing the extracted grapheme(s) and the next byte position to use
 * for subsequent calls. If the end of the string is reached, null will be returned.
 *
 * Previously known in PHP as `grapheme_extract`.
 */
function extract(string $string, int $size, int $start = 0): ?(string, int) {
  $next = 0;
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  $result = \grapheme_extract(
    $string,
    $size,
    0, /* $type: count graphemes */
    $start,
    inout $next,
  );
  if ($result === false) {
    return null;
  }
  return tuple($result, $next as nonnull);
}
