<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Str\Utf8;

use namespace HH\Lib\_Private;
use type HH\Lib\Str\Encoding;

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * If no length is given, the slice will contain the rest of the
 * string. If the length is zero, the empty string will be returned. If the
 * offset is out-of-bounds, a ViolationException will be thrown.
 *
 * Previously known as `mb_substr` in PHP.
 */
<<__RxLocal>>
function slice(string $string, int $offset, ?int $length = null): string {
  invariant($length === null || $length >= 0, 'Expected non-negative length.');
  $offset = _Private\validate_offset($offset, length($string));
  $result = $length === null
    ? \mb_substr($string, $offset, Encoding::UTF8)
    : \mb_substr($string, $offset, $length, Encoding::UTF8);
  if ($result === false) {
    return '';
  }
  return $result;
}

/**
 * Returns a substring of length `$length` of the given string starting at the
 * `$offset`.
 *
 * Operates on bytes instead of characters, unlike Str\Utf8\slice.
 * If the slice would take place in the middle of a multi-byte character,
 * the slice is performed starting from the first byte of that character.
 *
 * Previously known as `mb_strcut` in PHP.
 */
<<__RxLocal>>
function slice_bytes(string $string, int $offset, ?int $length = null): string {
  invariant($length === null || $length >= 0, 'Expected non-negative length.');
  $offset = _Private\validate_offset($offset, length($string));
  $result = $length === null
    ? \mb_strcut($string, $offset, Encoding::UTF8)
    : \mb_strcut($string, $offset, $length, Encoding::UTF8);
  if ($result === false) {
    return '';
  }
  return $result;
}

