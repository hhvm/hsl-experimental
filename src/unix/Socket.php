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

use namespace HH\Lib\Experimental\{IO, Network};

/** A Unix socket for a server or client connection.
 *
 * @see `Unix\Server` to accept new connections
 * @see `Unix\connect_async()` and `Unix\connect_nd_async()` to conenct to an
 *   existing server.
 */
<<__Sealed(DisposableSocket::class, CloseableSocket::class)>>
interface Socket extends Network\Socket {
  /** A file path */
  const type TAddress = string;
}
