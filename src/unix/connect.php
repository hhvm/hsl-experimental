<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Unix;

use namespace HH\Lib\Experimental\Network;

async function connect_nd_async(
  string $path,
  ConnectOptions $opts = shape(),
): Awaitable<NonDisposableSocket> {
  /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
  $sock = \socket_create(\AF_UNIX, \SOCK_STREAM, 0);
  /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
  if ($sock is resource) {
    $err = await Network\_Private\socket_connect_async(
      $sock,
      $path,
      0,
      $opts['timeout'] ?? null,
    );
    if ($err === 0) {
      return new _Private\NonDisposableSocket($sock);
    }
  } else {
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    $err = \socket_last_error() as int;
  }
  Network\_Private\throw_socket_error('connecting to socket', $err);
}

<<__ReturnDisposable>>
async function connect_async(string $path): Awaitable<DisposableSocket> {
  $nd = await connect_nd_async($path);
  return new _Private\DisposableSocket($nd);
}
