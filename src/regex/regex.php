<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Regex;

use namespace HH\Lib\Str;

final class Exception extends \Exception {
  public function __construct<T>(Pattern<T> $pattern): void {
    static $errors = dict[
      \PREG_INTERNAL_ERROR => 'Internal error',
      \PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit error',
      \PREG_RECURSION_LIMIT_ERROR => 'Recursion limit error',
      \PREG_BAD_UTF8_ERROR => 'Bad UTF8 error',
      \PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF8 offset error',
    ];
    parent::__construct(
      Str\format(
        "%s: %s",
        idx($errors, \preg_last_error(), 'Invalid pattern'),
        /* HH_FIXME[4110] Until we have a to_string() function */
        $pattern,
      ),
    );
  }
}

// TODO(T19708752): Tag appropriate functions as reactive once we've gotten rid of preg_match

/**
 * Temporary stand-in for native match function to be implemented in T30991246.
 * Returns the first match found in `$haystack` given the regex pattern `$pattern`
 * and an optional offset at which to start the search.
 *
 * Throws Invariant[Violation]Exception if `$offset` is not within plus/minus the length of `$haystack`
 * Returns null, or a tuple of
 * first,
 *   a Match containing
 *     - the entire matching string, at key 0,
 *     - the results of unnamed capture groups, at integer keys corresponding to
 *         the groups' occurrence within the pattern, and
 *     - the results of named capture groups, at string keys matching their respective names,
 * and second,
 *   the integer offset at which this first match occurs in the haystack string.
 */
function match_base<T as Match>(
  string $haystack,
  Pattern<T> $pattern,
  int $offset = 0,
): ?(T, int) {
  $offset = \HH\Lib\_Private\validate_offset($offset, Str\length($haystack));
  $match = darray[];
  $status =
    @\preg_match($pattern, $haystack, &$match, \PREG_OFFSET_CAPTURE, $offset);
  if ($status === 1) {
    $match_out = darray[];
    foreach ($match as $key => $value) {
      $match_out[$key] = $value[0];
    }
    $offset_out = $match[0][1];
    /* HH_FIXME[4110] Native function won't have this problem */
    return tuple($match_out, $offset_out);
  } else if ($status === 0) {
    return null;
  } else {
    throw new Exception($pattern);
  }
}

/**
 * Returns the first match found in `$haystack` given the regex pattern `$pattern`
 * and an optional offset at which to start the search.
 *
 * Throws Invariant[Violation]Exception if `$offset` is not within plus/minus the length of `$haystack`.
 * Returns null if there is no match, or a Match containing
 *    - the entire matching string, at key 0,
 *    - the results of unnamed capture groups, at integer keys corresponding to
 *        the groups' occurrence within the pattern, and
 *    - the results of named capture groups, at string keys matching their respective names.
 */
function match<T as Match>(
  string $haystack,
  Pattern<T> $pattern,
  int $offset = 0,
): ?T {
  return match_base($haystack, $pattern, $offset)[0] ?? null;
}

/**
 * Returns all matches found in `$haystack` given the regex pattern `$pattern`
 * and an optional offset at which to start the search.
 *
 * Throws Invariant[Violation]Exception if `$offset` is not within plus/minus the length of `$haystack`.
 */
function match_all<T as Match>(
  string $haystack,
  Pattern<T> $pattern,
  int $offset = 0,
): \Generator<int, T, void> {
  $haystack_length = Str\length($haystack);
  while ($match = match_base($haystack, $pattern, $offset)) {
    $captures = $match[0];
    yield $captures;
    $match_begin = $match[1];
    /* HH_FIXME[4108] Until we can define Match to have field 0 */
    /* HH_FIXME[4110] Until we can define Match to have field 0 */
    $match_length = Str\length($captures[0]);
    if ($match_length === 0) {
      $offset = $match_begin + 1;
      if ($offset > $haystack_length) {
        break;
      }
    } else {
      $offset = $match_begin + $match_length;
    }
  }
}

/**
 * Returns whether a match exists in `$haystack` given the regex pattern `$pattern`
 * and an optional offset at which to start the search.
 *
 * Throws Invariant[Violation]Exception if `$offset` is not within plus/minus the length of `$haystack`.
 */
function matches<T as Match>(
  string $haystack,
  Pattern<T> $pattern,
  int $offset = 0,
): bool {
  return match_base($haystack, $pattern, $offset) !== null;
}

/**
 * Returns `$haystack` with any substring matching `$pattern`
 * replaced by `$replacement`. If `$offset` is given, replacements are made
 * only starting from `$offset`.
 *
 * Throws Invariant[Violation]Exception if `$offset` is not within plus/minus the length of `$haystack`.
 */
function replace<T as Match>(
  string $haystack,
  Pattern<T> $pattern,
  string $replacement,
  int $offset = 0,
): string {
  // replace is the only one of these functions that calls into a preg
  // function other than preg_match. It needs to call into preg_replace
  // to be able to handle backreferencing in the `$replacement` string.
  // preg_replace does not support offsets, so we handle them ourselves,
  // consistently with match_base.
  $offset = \HH\Lib\_Private\validate_offset($offset, Str\length($haystack));
  $haystack1 = Str\slice($haystack, 0, $offset);
  $haystack2 = Str\slice($haystack, $offset);

  $haystack3 = @\preg_replace($pattern, $replacement, $haystack2);
  if ($haystack3 === null) {
    throw new Exception($pattern);
  }
  return $haystack1.$haystack3;
}

/**
 * Returns `$haystack` with any substring matching `$pattern`
 * replaced by the result of `$replace_func` applied to that match.
 * If `$offset` is given, replacements are made only starting from `$offset`.
 *
 * Throws Invariant[Violation]Exception if `$offset` is not within plus/minus the length of `$haystack`.
 */
function replace_with<T as Match>(
  string $haystack,
  Pattern<T> $pattern,
  (function(T): string) $replace_func,
  int $offset = 0,
): string {
  $haystack_length = Str\length($haystack);
  $result = Str\slice($haystack, 0, 0);
  $match_end = 0;
  while ($match = match_base($haystack, $pattern, $offset)) {
    $captures = $match[0];
    $match_begin = $match[1];
    // Copy anything between the previous match and this one
    $result .= Str\slice($haystack, $match_end, $match_begin - $match_end);
    $result .= $replace_func($captures);
    /* HH_FIXME[4108] Until we can define Match to have field 0 */
    /* HH_FIXME[4110] Until we can define Match to have field 0 */
    $match_length = Str\length($captures[0]);
    $match_end = $match_begin + $match_length;
    if ($match_length === 0) {
      // To get the next match (and avoid looping forever), need to skip forward
      // before searching again
      // Note that `$offset` is for searching and `$match_end` is for copying
      $offset = $match_begin + 1;
      if ($offset > $haystack_length) {
        break;
      }
    } else {
      $offset = $match_end;
    }
  }
  $result .= Str\slice($haystack, $match_end);
  return $result;
}

/**
 * Splits `$haystack` into chunks by its substrings that match with `$pattern`.
 * If `$limit` is given, the returned vec will have at most that many elements.
 * The last element of the vec will be whatever is left of the haystack string
 * after the appropriate number of splits.
 * If no substrings of `$haystack` match `$delimiter`, a vec containing only `$haystack` will be returned.
 *
 * Throws Invariant[Violation]Exception if `$limit` < 2.
 */
function split<T as Match>(
  string $haystack,
  Pattern<T> $delimiter,
  ?int $limit = null,
): vec<string> {
  if ($limit === null) {
    $limit = \INF;
  }
  invariant(
    $limit > 1,
    'Expected limit greater than 1, got %d.',
    $limit,
  );
  $haystack_length = Str\length($haystack);
  $result = vec[];
  $offset = 0;
  $match_end = 0;
  $count = 1;
  $match = match_base($haystack, $delimiter, $offset);
  while ($match && $count < $limit) {
    $captures = $match[0];
    $match_begin = $match[1];
    // Copy anything between the previous match and this one
    $result[] = Str\slice($haystack, $match_end, $match_begin - $match_end);
    /* HH_FIXME[4108] Until we can define Match to have field 0 */
    /* HH_FIXME[4110] Until we can define Match to have field 0 */
    $match_length = Str\length($captures[0]);
    $match_end = $match_begin + $match_length;
    if ($match_length === 0) {
      // To get the next match (and avoid looping forever), need to skip forward
      // before searching again
      // Note that `$offset` is for searching and `$match_end` is for copying
      $offset = $match_begin + 1;
      if ($offset > $haystack_length) {
        break;
      }
    } else {
      $offset = $match_end;
    }
    $count++;
    $match = match_base($haystack, $delimiter, $offset);
  }
  $result[] = Str\slice($haystack, $match_end);
  return $result;
}
