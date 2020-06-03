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

use namespace HH\Lib\{_Private, Keyset, Str, Dict};
use type HH\Lib\Experimental\Str\Encoding;

/**
 * Returns the string with all alphabetic characters converted to uppercase.
 */
<<__RxLocal>>
function uppercase(string $string): string {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_strtoupper($string, Encoding::UTF8);
}

/**
 * Returns the string with all alphabetic characters converted to lowercase.
 */
<<__RxLocal>>
function lowercase(string $string): string {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_strtolower($string, Encoding::UTF8);
}

/**
 * Convert the string from the specified encoding to UTF-8
 */
<<__RxLocal>>
function from_encoding(string $string, Encoding $encoding): string {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_convert_encoding($string, Encoding::UTF8 as string, $encoding);
}

/**
 * Convert the string from UTF-8 to the specified encoding
 */
<<__RxLocal>>
function to_encoding(string $string, Encoding $encoding): string {
  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_convert_encoding($string, $encoding as string, Encoding::UTF8);
}

type TConvertKanaOption = shape(
  /**
   * Convert "zen-kaku" alphabets to "han-kaku"
   */
  ?'r' => bool,
  /**
   * Convert "han-kaku" alphabets to "zen-kaku"
   */
  ?'R' => bool,
  /**
   * Convert "zen-kaku" numbers to "han-kaku"
   */
  ?'n' => bool,
  /**
   * Convert "han-kaku" numbers to "zen-kaku"
   */
  ?'N' => bool,
  /**
   * Convert "zen-kaku" alphabets and numbers to "han-kaku"
   */
  ?'a' => bool,
  /**
   * Convert "han-kaku" alphabets and numbers to "zen-kaku" (Characters
   * included in "a", "A" options are U+0021 - U+007E excluding U+0022,
   * U+0027, U+005C, U+007E)
   */
  ?'A' => bool,
  /**
   * Convert "zen-kaku" space to "han-kaku" (U+3000 -> U+0020)
   */
  ?'s' => bool,
  /**
   * Convert "han-kaku" space to "zen-kaku" (U+0020 -> U+3000)
   */
  ?'S' => bool,
  /**
   * Convert "zen-kaku kata-kana" to "han-kaku kata-kana"
   */
  ?'k' => bool,
  /**
   * Convert "han-kaku kata-kana" to "zen-kaku kata-kana"
   */
  ?'K' => bool,
  /**
   * Convert "zen-kaku hira-gana" to "han-kaku kata-kana"
   */
  ?'h' => bool,
  /**
   * Convert "han-kaku kata-kana" to "zen-kaku hira-gana"
   */
  ?'H' => bool,
  /**
   * Convert "zen-kaku kata-kana" to "zen-kaku hira-gana"
   */
  ?'c' => bool,
  /**
   * Convert "zen-kaku hira-gana" to "zen-kaku kata-kana"
   */
  ?'C' => bool,
  /**
   * Collapse voiced sound notation and convert them into a character.
   * Use with "K", "H"
   */
  ?'V' => bool,
);

/**
 * Performs a "han-kaku" - "zen-kaku" conversion for string str. This function
 *   is only useful for Japanese.
 *
 * See TConvertKanaOption for the list of options
 */
<<__RxLocal>>
function convert_kana(string $string, TConvertKanaOption $options): string {
  // the native implementation wants a string containing all the options
  // order does not matter
  // take only true elements from the shape and join them
  $option_string = Shapes::toDict($options)
    |> Dict\filter($$)
    |> Keyset\keys($$)
    |> Str\join($$, '');

  /* HH_FIXME[2049] __PHPStdLib */
  /* HH_FIXME[4107] __PHPStdLib */
  return \mb_convert_kana($string, $option_string, Encoding::UTF8);
}

/**
 * Return the string with a slice specified by the offset/length replaced by the
 * given replacement string.
 *
 * If the length is omitted or exceeds the upper bound of the string, the
 * remainder of the string will be replaced. If the length is zero, the
 * replacement will be inserted at the offset.
 */
<<__RxLocal>>
function splice(
  string $string,
  string $replacement,
  int $offset,
  ?int $length = null,
): string {
  invariant($length === null || $length >= 0, 'Expected non-negative length.');
  $total_length = length($string);
  $offset = _Private\validate_offset($offset, $total_length);
  if ($length === null || ($offset + $length) >= $total_length) {
    return slice($string, 0, $offset).$replacement;
  }
  return slice($string, 0, $offset).
    $replacement.
    slice($string, $offset + $length);
}
