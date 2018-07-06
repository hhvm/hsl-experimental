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
 * @param Pattern $pattern - The regular expression to match on
 * @param int $offset (= 0) - The offset within $haystack at which to start the search
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

/**
 * Returns the first match found in a string given a regex pattern, and optionally,
 * an offset at which to start searching.
 *
 * @param string $haystack - The string to be searched
 * @param Pattern $pattern - The regular expression to match on
 * @param int $offset (= 0) - The offset within $haystack at which to start the search
 *
 * @return ?Match - Null, or a Match representing the first match to occur
 *  $haystack after $offset, which will contain
 *    - the entire matching string, at key 0,
 *    - the results of unnamed capture groups, at integer keys corresponding to
 *        the groups' occurrence within the pattern, and
 *    - the results of named capture groups, at keys that match their respective
 *        names (and temporarily, also at integer keys like for unnamed capture groups)
 */
function match<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): ?T {
  return match_base($haystack, $pattern, $offset)[0] ?? null;
}

/**
 * Returns all matches in a string given a regex pattern, and optionally,
 * an offset at which to start searching.
 *
 * @param string $haystack - The string to be searched
 * @param Pattern $pattern - The regular expression to match on
 * @param int $offset (= 0) - The offset within $haystack at which to start the search
 *
 * @return Generator<mixed, Match, void> - Generator for Matches in the order
 * that they occur in $haystack after the $offset. (See match for specifics on
 * Match.)
 */
function match_all<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): \Generator<int, T, void> {
  while ($match = match_base($haystack, $pattern, $offset)) {
    yield $match[0];
    // start from end of last match
    $offset = $match[1] + Str\length($match[0][0]);
  }
}

/**
 * Returns whether a match exists in a string given a regex pattern, and optionally,
 * an offset at which to start searching.
 *
 * @param string $haystack - The string to be searched
 * @param Pattern $pattern - The regular expression to match on
 * @param int $offset (= 0) - The offset within $haystack at which to start the search
 *
 * @return bool - true if $haystack matches $pattern anywhere after $offset
 */
function matches<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): bool {
  return match_base($haystack, $pattern, $offset) !== null;
}

/**
 * Returns the given string, but with any substring matching a given regex pattern
 * replaced by the replacement string. If an offset is given, replacements are made
 * only starting from that offset.
 *
 * @param string $haystack - The string to be searched
 * @param Pattern $pattern - The regular expression to match on
 * @param string $replacement - The string to replace
 * @param int $offset (= 0) - The offset within $haystack at which to start the search
 *
 * @return string - $haystack with all matching substrings replaced with $replacement
 */
// TODO(T19708752): Implement backreferencing. May need native replace function.
function replace<T as Match>(
  string $haystack,
  Pattern $pattern,
  string $replacement,
  int $offset = 0,
): string {
  $result = Str\slice($haystack, 0, 0);
  $match_end = 0;
  $match = match_base($haystack, $pattern, $offset);
  while ($match) {
    $match_begin = $match[1];
    $result .= Str\slice($haystack, $match_end, $match_begin - $match_end);
    $result .= $replacement;
    $match_end = $match_begin + Str\length($match[0][0]);
    $match = match_base($haystack, $pattern, $match_end);
  }
  $result .= Str\slice($haystack, $match_end);
  return $result;
}

/**
 * Returns the given string, but with any substring matching a given regex pattern
 * replaced by the result of the replacement function applied to that match.
 * If an offset is given, replacements are made only starting from that offset.
 *
 * @param string $haystack - The string to be searched
 * @param Pattern $pattern - The regular expression to match on
 * @param function(Match): string $replace_func - The function to modify matching substrings with
 * @param int $offset (= 0) - The offset within $haystack at which to start the search
 *
 * @return string - $haystack with every matching substring replaced with $replace_func
 * applied to that match
 */
function replace_with<T as Match>(
  string $haystack,
  Pattern $pattern,
  (function(T): string) $replace_func,
  int $offset = 0,
): string {
  $result = Str\slice($haystack, 0, 0);
  $match_end = 0;
  $match = match_base($haystack, $pattern, $offset);
  while ($match) {
    $match_begin = $match[1];
    $result .= Str\slice($haystack, $match_end, $match_begin - $match_end);
    $result .= $replace_func($match[0]);
    $match_end = $match_begin + Str\length($match[0][0]);
    $match = match_base($haystack, $pattern, $match_end);
  }
  $result .= Str\slice($haystack, $match_end);
  return $result;
}

/**
 * Splits a given string by a regular expression. If a limit is given, the returned
 * vec will have at most that many elements.
 *
 * @param string $haystack - The string to be split
 * @param Pattern $delimiter - The regular expression to match and split on
 * @param int $limit (= null) - If specified, then the returned vec will
 * have at most $limit elements. The last element of the vec will be whatever
 * is left of the haystack string after the appropriate number of splits.
 * $limit must be > 1
 *
 * @throws Invariant[Violation]Exception - If $limit <= 0
 * @return vec<string> - vec containing (at most $limit, if $limit is given)
 * substrings in $haystack, delimited by substrings matching $delimiter; whatever
 * is left of $haystack after the limit is reached makes up the last substring in the vec.
 * If no substrings of $haystack match $delimiter, a vec containing only $haystack is returned
 */
function split<T as Match>(
  string $haystack,
  Pattern $delimiter,
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
  $result = vec[];
  $match_end = 0;
  $count = 1;
  $match = match_base($haystack, $delimiter);
  while ($match && $count < $limit) {
    $captures = $match[0];
    $match_begin = $match[1];
    $result[] = Str\slice($haystack, $match_end, $match_begin - $match_end);
    $match_end = $match_begin + Str\length($captures[0]);
    if ($count !== $limit) {
      $match = match_base($haystack, $delimiter, $match_end);
    }
    $count += 1;
  }
  $result[] = Str\slice($haystack, $match_end);
  return $result;
}
