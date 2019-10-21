<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\UnixSocket\_Private;

use namespace HH\Lib\Experimental\{IO, UnixSocket};

final class DisposableSocket
  extends IO\_Private\DisposableHandleWrapper<UnixSocket\NonDisposableSocket>
  implements
    \IAsyncDisposable,
    IO\DisposableReadWriteHandle,
    UnixSocket\DisposableSocket {

  use IO\_Private\DisposableReadHandleWrapperTrait<
    UnixSocket\NonDisposableSocket,
  >;
  use IO\_Private\DisposableWriteHandleWrapperTrait<
    UnixSocket\NonDisposableSocket,
  >;

  public function __construct(UnixSocket\NonDisposableSocket $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): string {
    return $this->impl->getLocalAddress();
  }

  public function getPeerAddress(): string {
    return $this->impl->getPeerAddress();
  }
}
