<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Debug;

use namespace HH\Lib\IO;

/** Return a human-readable string representation of a value */
function dump_s(<<__AcceptDisposable>> mixed $value): string {
  /* HH_FIXME[2049] */
  /* HH_FIXME[4107] */
  return \print_r(
   /* HH_FIXME[4188] disposable as non-disposable */ $value,
   /* return = */ true,
  );
}

/** Print a human-readable representation of a value.
 *
 * The request error stream is used if available; otherwise, request output
 * is used instead.
 *
 * This is the HTTP response for HTTP requests, or STDERR for CLI.
 */
async function dump_async(
  <<__AcceptDisposable>> mixed $value,
): Awaitable<void> {
  await dump_to_async($value, IO\request_error() ?? IO\request_output());
}

/** Print a human-readable representation of a value to the specified
 * stream. */
async function dump_to_async(
  <<__AcceptDisposable>> mixed $value,
  <<__AcceptDisposable>> IO\WriteHandle $handle,
): Awaitable<void> {
  await $handle->writeAsync(dump_s($value));
}

/** Print a human-readable representation of a value, and return it.
 *
 * The request stream is used if it is available; otherwise, request output is
 * used instead.
 *
 * This is the HTTP response for HTTP requests, or STDERR for CLI.
 */
async function tap_async<T>(
  T $value,
): Awaitable<T> {
  await dump_async($value);
  return $value;
}

/** Print a human-readable representation of a value to the request error
 * stream, and return it. */
async function tap_to_async<T>(
  T $value,
  <<__AcceptDisposable>> IO\WriteHandle $handle,
): Awaitable<T> {
  await dump_to_async($value, $handle);
  return $value;
}
