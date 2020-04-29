<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_Network;

use namespace HH\Lib\{Network, OS};
use namespace HH\Lib\_Private\_OS;
use type HH\Lib\_Private\PHPWarningSuppressor;

/** Create a server socket and start listening */
async function socket_create_bind_listen_async(
  OS\SocketDomain $domain,
  OS\SocketType $type,
  int $proto,
  OS\sockaddr $addr,
  int $backlog,
  Network\SocketOptions $socket_options,
): Awaitable<OS\FileDescriptor> {
  $sock = OS\socket($domain, $type, $proto);
  // FIXME: use setsockopt when supported
  invariant(
    $socket_options === shape(),
    "Socket options are not currently supported",
  );
  $ops = OS\fcntl($sock, OS\FcntlOp::F_GETFL);
  OS\fcntl($sock, OS\FcntlOp::F_SETFL, ($ops as int) | OS\O_NONBLOCK);

  try {
    OS\bind($sock, $addr);
  } catch (OS\BlockingIOException $_) {
    await _OS\poll_async($sock, \STREAM_AWAIT_READ_WRITE, /* timeout = */ 0);

    // FIXME: use getsockopt($sock, SOL_SOCKET, SO_ERROR) when supported.
    // for now, the listen will fail with `EDESTADDRREQ`; this isn't as
    // detailed, but works.
  }

  OS\listen($sock, $backlog);

  return $sock;
}
