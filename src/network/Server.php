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

/** Generic interface for a class able to accept socket connections.
 *
 * @see Unix\Server
 * @see TCP\Server
 */
interface Server<
  TSock as Socket,
  TDSock as TSock as DisposableSocket,
  TNDSock as TSock as CloseableSocket,
> {
  /** The type of address used by this socket.
   *
   * For example, this is likely to be a string path for Unix sockets,
   * or hostname and port for TCP sockets.
   */
  abstract const type TAddress;

  /** Retrieve the next pending connection as a disposable.
   *
   * Will wait for new connections if none are pending.
   *
   * @see `nextConnectionNDAsync()` for non-disposables.
   */
  <<__ReturnDisposable>>
  public function nextConnectionAsync(): Awaitable<TDSock>;
  /** Retrieve the next pending conenction.
   *
   * Will wait for new connections if none are pending.
   * @see `nextConnectionAsync()` for a disposable
   */
  public function nextConnectionNDAsync(): Awaitable<TNDSock>;

  /** Return the local (listening) address for the server */
  public function getLocalAddress(): this::TAddress;
}
