<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Unix;

use namespace HH\Lib\Network;
use namespace HH\Lib\_Private\{_Network, _Unix};

/** Asynchronously connect to the specified unix socket, returning a
 * non-disposable handle.
 *
 * @see connect_async()
 */
async function connect_nd_async(
  string $path,
  ConnectOptions $opts = shape(),
): Awaitable<CloseableSocket> {
  /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
  $sock = \socket_create(\AF_UNIX, \SOCK_STREAM, 0);
  /* HH_IGNORE_ERROR[2049] PHP STDLib */
  /* HH_IGNORE_ERROR[4107] PHP STDLib */
  if ($sock is resource) {
    $err = await _Network\socket_connect_async(
      $sock,
      $path,
      0,
      $opts['timeout'] ?? null,
    );
    if ($err === 0) {
      return new _Unix\CloseableSocket($sock);
    }
  } else {
    /* HH_IGNORE_ERROR[2049] PHP STDLib */
    /* HH_IGNORE_ERROR[4107] PHP STDLib */
    $err = \socket_last_error() as int;
  }
  _Network\throw_socket_error($err, 'connect() failed');
}

/** Asynchronously connect to the specified unix socket, returning a disposable
 * handle.
 *
 * @see connect_nd_async()
 */
<<__ReturnDisposable>>
async function connect_async(string $path): Awaitable<DisposableSocket> {
  $nd = await connect_nd_async($path);
  return new _Unix\DisposableSocket($nd);
}
