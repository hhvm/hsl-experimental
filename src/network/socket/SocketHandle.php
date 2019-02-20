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

use namespace HH\Lib\Experimental\IO;

/**
 * Contract for a reliable socket-based handle.
 */
interface SocketHandle extends Socket, IO\ReadHandle, IO\WriteHandle {
  /**
  * Get the address of the remote peer.
  */
  public function getRemoteAddress(): Host;

  /**
   * Get the network port used by the remote peer (or NULL if not network port is being used).
   */
  public function getRemotePort(): ?Port;

  /**
   * Place the given data in the socket's send queue.
   */
  <<__Override>>
  public function writeAsync(string $data): Awaitable<void>;
}
