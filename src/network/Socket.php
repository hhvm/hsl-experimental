<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\Experimental\{IO, TCP, Unix};

<<__Sealed(
  DisposableSocket::class,
  CloseableSocket::class,
  TCP\Socket::class,
  Unix\Socket::class,
)>>
interface Socket extends IO\ReadWriteHandle {
  /** A local or peer address.
   *
   * For IP-based sockets, this is likely to be a host and port;
   * for Unix sockets, it is likely to be a filesystem path.
   */
  abstract const type TAddress;

  /** Returns the local address and port */
  public function getLocalAddress(): this::TAddress;
  /** Returns the remote address and port */
  public function getPeerAddress(): this::TAddress;
}
