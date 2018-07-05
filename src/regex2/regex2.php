<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Regex2;

use namespace HH\Lib\Str;

use type HH\InvariantException as InvalidRegexException; // @oss-enable
// @oss-disable: use type \InvalidRegexException;

// TODO(T19708752): Lift restriction and make Pattern generic with constrained type parameter
// TODO(T19708752): Tag appropriate functions as reactive once we've gotten rid of preg_match
/* HH_FIXME[4137] Will deal with integer field names restriction later */
newtype Match as shape(...) = shape(...);
newtype Pattern as string = string;

/* Mock `re`-prefixed strings */
function re(string $pattern_string) : Pattern {
  return $pattern_string;
}

/**
 * Temporary stand-in for native match function to be implemented in T30991246.
 *
 * @param string $haystack - The string to be searched
 * @param Pattern $offset - The regular expression to match on
 * @param int $offset - The offset within $haystack at which to start the search
 *
 * Returns null, or a tuple of
 * first,
 *   a Match representing the first match to occur in the haystack after the
 *   given offset, which will contain
 *    - the entire matching string, at key 0,
 *    - the results of unnamed capture groups, at integer keys corresponding to
 *        the groups' occurrence within the pattern, and
 *    - the results of named capture groups, at keys that match their respective
 *        names (and temporarily, also at integer keys like for unnamed capture groups);
 * and second,
 *   the integer offset at which this first match occurs in the haystack string.
 *
 * @return ?(Match, int) - Null, or the match and the offset at which it occurs
 * in the haystack string.
 */
function match_base<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): ?(T, int) {
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
    static $errors = dict[
      \PREG_INTERNAL_ERROR => 'Internal error',
      \PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit error',
      \PREG_RECURSION_LIMIT_ERROR => 'Recursion limit error',
      \PREG_BAD_UTF8_ERROR => 'Bad UTF8 error',
      \PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF8 offset error',
    ];
    throw new InvalidRegexException(
Str\format( // @oss-enable
      '%s: %s',
      idx($errors, \preg_last_error(), 'Invalid pattern'),
      $pattern,
), // @oss-enable
    );
  }
}

function match<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): ?T {
  return match_base($haystack, $pattern, $offset)[0] ?? null;
}

function match_all<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): \Generator<int, T, void> {
  invariant_violation('Not implemented yet.');
}

function matches<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): bool {
  return match_base($haystack, $pattern, $offset) !== null;
}

function replace<T as Match>(
  string $haystack,
  Pattern $pattern,
  string $replacement,
  int $offset = 0,
): string {
  invariant_violation('Not implemented yet.');
}

function replace_with<T as Match>(
  string $haystack,
  Pattern $pattern,
  (function(T): string) $replace_func,
  int $offset = 0,
): string {
  invariant_violation('Not implemented yet.');
}

function split<T as Match>(
  string $haystack,
  Pattern $delimiter,
  ?int $limit = null,
): vec<string> {
  invariant_violation('Not implemented yet.');
}
