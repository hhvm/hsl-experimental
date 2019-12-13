<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\TCP\_Private;

use namespace HH\Lib\Experimental\{IO, TCP};

final class DisposableTCPSocket
  extends IO\_Private\DisposableHandleWrapper<TCP\CloseableSocket>
  implements
    \IAsyncDisposable,
    IO\DisposableReadWriteHandle,
    TCP\DisposableSocket {

  use IO\_Private\DisposableReadHandleWrapperTrait<TCP\CloseableSocket>;
  use IO\_Private\DisposableWriteHandleWrapperTrait<TCP\CloseableSocket>;

  public function __construct(TCP\CloseableSocket $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): (string, int) {
    return $this->impl->getLocalAddress();
  }

  public function getPeerAddress(): (string, int) {
    return $this->impl->getPeerAddress();
  }
}
