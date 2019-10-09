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

use namespace HH\Lib\_Private;
use namespace HH\Lib\Experimental\Network;

async function connect_nd_async(
  string $host,
  int $port,
  Network\IPProtocolBehavior $ipver = Network\IPProtocolBehavior::PREFER_IPV6,
): Awaitable<NonDisposableSocket> {
  // TODO: implement a true async native `connect()` function

  if ($ipver !== Network\IPProtocolBehavior::FORCE_IPV4) {
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    $sock = \socket_create(\AF_INET6, \SOCK_STREAM, \SOL_TCP);
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    if ($sock is resource && \socket_connect($sock, $host, $port)) {
      return new _Private\NonDisposableTCPSocket($sock);
    }
  }
  if ($ipver === Network\IPProtocolBehavior::FORCE_IPV6) {
    throw new \Exception(
      /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
      "Failed to connect: ".\socket_strerror(\socket_last_error()),
    );
  }
  // We either have FORCE_IPV4, or PREFER_IPV6 but we failed to connect
  /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
  $sock = \socket_create(\AF_INET, \SOCK_STREAM, \SOL_TCP);
  /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
  if ($sock is resource && \socket_connect($sock, $host, $port)) {
    return new _Private\NonDisposableTCPSocket($sock);
  }
  throw new \Exception(
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
    "Failed to connect: ".\socket_strerror(\socket_last_error()),
  );
}

<<__ReturnDisposable>>
async function connect_async(
  string $host,
  int $port,
  Network\IPProtocolBehavior $ipver = Network\IPProtocolBehavior::PREFER_IPV6,
): Awaitable<DisposableSocket> {
  $nd = await connect_nd_async($host, $port, $ipver);
  return new _Private\DisposableTCPSocket($nd);
}
