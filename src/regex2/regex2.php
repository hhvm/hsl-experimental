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

/* Mock native match function */
function match_base<T as Match>(
  string $haystack,
  Pattern $pattern,
  int $offset = 0,
): ?(T, int) {
  invariant_violation('Not implemented yet');
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
