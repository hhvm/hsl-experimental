<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

<<__Sealed(
  Server::class,
  UdpSocket::class,
  SocketHandle::class
)>>
interface Socket {
  /**
   * Close the underlying socket.
   */
  public function close(): void;

  /**
   * Get the local address of the socket.
   */
  public function getAddress(): string;

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?int;

  /**
   * Get socket type.
   */
  public function getType(): SocketType;
}