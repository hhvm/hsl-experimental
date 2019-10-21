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

interface Server<
  TSock as Socket,
  TDSock as TSock as DisposableSocket,
  TNDSock as TSock as NonDisposableSocket,
> {
  abstract const type TAddress;

  <<__ReturnDisposable>>
  public function nextConnectionAsync(): Awaitable<TDSock>;
  public function nextConnectionNDAsync(): Awaitable<TNDSock>;

  public function getLocalAddress(): this::TAddress;
}
