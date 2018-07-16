<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Regex;

use namespace HH\Lib\Str;

use type HH\InvariantException as InvalidRegexException; // @oss-enable
// @oss-disable: use type \InvalidRegexException;

/**
 * Execute a regular expression match and returns matches or null in case there
 * is no match
 *
 * Replacement for preg_match
 *
 * @example
 * Regex\match('test', '#(t.)s#')
 * -> dict[0 => 'tes', 1 => 'te']
 */
<<__RxLocal>>
function match(
  string $haystack,
  string $pattern,
  int $offset = 0,
): ?dict<arraykey, string> {
  $captures = darray[];
  /* HH_FIXME[2088] No refs in reactive code. */
  $return = @\preg_match($pattern, $haystack, &$captures, 0, $offset);
  __verify(\is_int($return), $pattern);
  if ($return === 0) {
    return null;
  }

  return dict($captures);
}

/**
 * Execute a regular expression match and returns true or false depending if the
 * pattern matched or not
 *
 * @example
 * Regex\is_match('test', '#(t.)s#')
 * -> true
 */
<<__RxLocal>>
function is_match(
  string $haystack,
  string $pattern,
  int $offset = 0,
): bool {
  $captures = darray[];
  /* HH_FIXME[2088] No refs in reactive code. */
  $return = @\preg_match($pattern, $haystack, &$captures, 0, $offset);
  __verify(\is_int($return), $pattern);
  return $return !== 0;
}

/**
 * Execute a regular expression replacement. Replaces the pattern with
 * replacement
 *
 * Replacement for preg_replace
 *
 * @example
 * Regex\replace('test', '#t.s#', 'abc')
 * -> 'abct'
 */
<<__RxLocal>>
function replace(
  string $haystack,
  string $pattern,
  string $replacement,
  int $limit = -1,
): string {
  $return = @\preg_replace($pattern, $replacement, $haystack, $limit);
  __verify(\is_string($return), $pattern);
  return $return;
}

/**
 * Execute a regular expression match and returns all matches or empty vec if
 * there is no matches
 *
 * Replacement for preg_match_all
 *
 * @example
 * Regex\match_all('test t3st', '#(t.)s#')
 * -> vec[dict[0 => 'tes', 1 => 'te'], dict[0 => 't3s', 1 => 't3']]
 */
<<__RxLocal>>
function match_all(
  string $haystack,
  string $pattern,
  int $offset = 0,
): vec<dict<arraykey, string>> {
  $captures = varray[];
  $return = @\preg_match_all(
    $pattern,
    $haystack,
    /* HH_FIXME[2088] No refs in reactive code. */
    &$captures,
    \PREG_SET_ORDER,
    $offset,
  );
  __verify(\is_int($return), $pattern);

  return \HH\Lib\Vec\map($captures, <<__Rx>> $capture ==> dict($capture));
}

/**
 * Execute a regular expression split. Returns a vec with all the parts.
 *
 * Replacement for preg_split
 *
 * @example
 * Regex\split('test t3st', '#.s#')
 * -> vec['t', 't t', 't']
 */
<<__RxLocal>>
function split(
  string $haystack,
  string $pattern,
): vec<string> {
  $return = @\preg_split($pattern, $haystack);
  __verify(\is_array($return), $pattern);
  return vec($return);
}

/**
 * Execute a regular expression quote. To be used to escape all special
 * characters that regex is using
 *
 * Replacement for preg_quote
 *
 * @example
 * Regex\quote('test[]')
 * -> 'test\[\]'
 */
<<__Rx>>
function quote(
  string $input,
  ?string $delimiter = null,
): string {
  return (string)\preg_quote($input, $delimiter);
}

/* Helper functions */

const dict<int, string> ERRORS = dict[
  \PREG_INTERNAL_ERROR => 'Internal error',
  \PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit error',
  \PREG_RECURSION_LIMIT_ERROR => 'Recursion limit error',
  \PREG_BAD_UTF8_ERROR => 'Bad UTF8 error',
  \PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF8 offset error',
];

function __verify(
  bool $ret,
  string $pattern,
): void {
  if ($ret === false) {
    throw new InvalidRegexException(
Str\format( // @oss-enable
      '%s: %s',
      (idx(namespace\ERRORS, \preg_last_error()) |> $$ !== null && $$ ? $$ : 'Invalid pattern'),
      $pattern,
), // @oss-enable
    );
  }
}
