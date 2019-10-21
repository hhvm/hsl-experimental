<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use type HH\Lib\Experimental\OS\Errno;

/** Asynchronously connect to a socket.
 *
 * Returns a PHP Socket Error Code:
 * - 0 for success
 * - errno if > 0
 * - -(10000 + h_errno) if < 0
 */
async function socket_connect_async(
  resource $sock,
  string $host,
  int $port,
  ?float $timeout_seconds,
): Awaitable<int> {
  // We return error codes and expect the user-facing functions to deal with
  // them. Don't spew PHP errors to logs.
  using new PHPWarningSuppressor();
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  \socket_set_blocking($sock, false);
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  $res = \socket_connect($sock, $host, $port);
  if ($res === true) {
    return 0;
  }
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  if (\socket_last_error($sock) !== Errno::EINPROGRESS) {
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    return \socket_last_error($sock);
  }
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  \socket_clear_error($sock);

  // connect(2) documents non-blocking sockets as being ready for write
  // when complete
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  $res = await \stream_await($sock, \STREAM_AWAIT_WRITE, $timeout_seconds ?? 0.0);
  if ($res === \STREAM_AWAIT_CLOSED) {
    return Errno::ECONNRESET as int;
  }
  if ($res === \STREAM_AWAIT_TIMEOUT) {
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    \fclose($sock);
    return Errno::ETIMEDOUT as int;
  }
  // \socket_last_error() is not populated by async socket failures: it's
  // effectively a cache of the C errno constant after the last socket_*
  // call - but given that the failure of an async connect is detected by
  //select - which has its' own use of errno, it can't be set the usual way.
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  return \socket_get_option($sock, \SOL_SOCKET, \SO_ERROR);
}
