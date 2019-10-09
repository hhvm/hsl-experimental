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

<<__Memoize>>
function einprogress(): int {
  // This is why we can't have nice things

  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  if (\php_uname('s') === 'Darwin') {
    return 36;
  }
  return 115;
}

/** Asynchronously connect to a socket.
 *
 * Returns an error code, or 0 on success.
 */
async function socket_connect_async(
  resource $sock,
  string $host,
  int $port,
): Awaitable<int> {
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
  if (\socket_last_error($sock) !== einprogress()) {
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
  await \stream_await($sock, \STREAM_AWAIT_WRITE);
  // \socket_last_error() is not populated by async socket failures: it's
  // effectively a cache of the C errno constant after the last socket_*
  // call - but given that the failure of an async connect is detected by
  //select - which has its' own use of errno, it can't be set the usual way.
  /* HH_IGNORE_ERROR[2049] PHP stdlib */
  /* HH_IGNORE_ERROR[4107] PHP stdlib */
  return \socket_get_option($sock, \SOL_SOCKET, \SO_ERROR);
}
