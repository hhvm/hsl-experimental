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
  \socket_set_blocking($sock, false);
  $res = \socket_connect($sock, $host, $port);
  if ($res === true) {
    return 0;
  }
  if (\socket_last_error($sock) !== einprogress()) {
    return \socket_last_error($sock);
  }
  \socket_clear_error($sock);

  // connect(2) documents non-blocking sockets as being ready for write
  // when complete
  await \stream_await($sock, \STREAM_AWAIT_WRITE);
  // \socket_last_error() is not populated by this
  return \socket_get_option($sock, \SOL_SOCKET, \SO_ERROR);
}
