<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_TCP;

use namespace HH\Lib\{IO, TCP};
use namespace HH\Lib\_Private\_IO;

final class DisposableTCPSocket
  extends _IO\DisposableHandleWrapper<TCP\CloseableSocket>
  implements
    \IAsyncDisposable,
    IO\DisposableReadWriteHandle,
    TCP\DisposableSocket {

  use _IO\DisposableReadHandleWrapperTrait<TCP\CloseableSocket>;
  use _IO\DisposableWriteHandleWrapperTrait<TCP\CloseableSocket>;

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
