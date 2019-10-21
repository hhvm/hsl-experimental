<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\TCP;

use namespace HH\Lib\Experimental\Network;

/** Connect to a socket asynchronously, returning a non-disposable handle.
 *
 * If using IPv6 with a fallback to IPv4 with a connection timeout, the timeout
 * will apply separately to the IPv4 and IPv6 connection attempts.
 */
async function connect_nd_async(
  string $host,
  int $port,
  ConnectOptions $opts = shape(),
): Awaitable<NonDisposableSocket> {
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
  foreach ($afs as $af) {
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    $sock = \socket_create($af, \SOCK_STREAM, \SOL_TCP);
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    if ($sock is resource) {
      $err = await Network\_Private\socket_connect_async($sock, $host, $port, $timeout);
      if ($err === 0) {
        return new namespace\_Private\NonDisposableTCPSocket($sock);
      }
    } else {
      /* HH_IGNORE_ERROR[2049] PHP STDLib */
      /* HH_IGNORE_ERROR[4107] PHP STDLib */
      $err = \socket_last_error() as int;
    }
  }

  Network\_Private\throw_socket_error('connecting to socket', $err);
}

/** Connect to a socket asynchronously, returning a disposable handle.
 *
 * If using IPv6 with a fallback to IPv4 with a connection timeout, the timeout
 * will apply separately to the IPv4 and IPv6 connection attempts.
 */
<<__ReturnDisposable>>
async function connect_async(
  string $host,
  int $port,
  ConnectOptions $opts = shape(),
): Awaitable<DisposableSocket> {
  $nd = await connect_nd_async($host, $port, $opts);
  return new _Private\DisposableTCPSocket($nd);
}
