<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\TCP;

use namespace HH\Lib\Network;
use namespace HH\Lib\_Private\{_Network, _TCP};

/** Connect to a socket asynchronously, returning a non-disposable handle.
 *
 * If using IPv6 with a fallback to IPv4 with a connection timeout, the timeout
 * will apply separately to the IPv4 and IPv6 connection attempts.
 */
async function connect_async(
  string $host,
  int $port,
  ConnectOptions $opts = shape(),
): Awaitable<CloseableSocket> {
  $ipver = $opts['ip_version'] ?? Network\IPProtocolBehavior::PREFER_IPV6;
  $timeout = $opts['timeout'] ?? null;
  switch ($ipver) {
    case Network\IPProtocolBehavior::PREFER_IPV6:
      /* HH_IGNORE_ERROR[2049] PHP STDLib */
      /* HH_IGNORE_ERROR[4107] PHP STDLib */
      $afs = vec[\AF_INET6, \AF_INET];
      break;
    case Network\IPProtocolBehavior::FORCE_IPV6:
      /* HH_IGNORE_ERROR[2049] PHP STDLib */
      /* HH_IGNORE_ERROR[4107] PHP STDLib */
      $afs = vec[\AF_INET6];
      break;
    case Network\IPProtocolBehavior::FORCE_IPV4:
      /* HH_IGNORE_ERROR[2049] PHP STDLib */
      /* HH_IGNORE_ERROR[4107] PHP STDLib */
      $afs = vec[\AF_INET];
      break;
  }

  $err = 0;
  $err_message = '';
  // TODO: refactor so that socket_connect_async() throws, and we catch and retry
  foreach ($afs as $af) {
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    \socket_clear_error();
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    $sock = \socket_create($af, \SOCK_STREAM, \SOL_TCP);
    // This must be *immediately* after the socket_create call, not in an else
    // block
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    $err = \socket_last_error();
    $err_message = "socket() failed";
    if ($sock is resource) {
      $err = await _Network\socket_connect_async($sock, $host, $port, $timeout);
      if ($err === 0) {
        return new _TCP\CloseableTCPSocket($sock);
      }
      $err_message = 'connect() failed';
    }
  }
  _Network\throw_socket_error($err, $err_message);
}

<<__Deprecated("Use connect_async()/gen_connect() instead")>>
async function connect_nd_async(
  string $host,
  int $port,
  ConnectOptions $opts = shape(),
): Awaitable<CloseableSocket> {
  return await connect_async($host, $port, $opts);
}
