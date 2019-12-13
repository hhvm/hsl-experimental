<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Unix\_Private;

use namespace HH\Lib\Experimental\{IO, Unix};

final class DisposableSocket
  extends IO\_Private\DisposableHandleWrapper<Unix\CloseableSocket>
  implements
    \IAsyncDisposable,
    IO\DisposableReadWriteHandle,
    Unix\DisposableSocket {

  use IO\_Private\DisposableReadHandleWrapperTrait<
    Unix\CloseableSocket,
  >;
  use IO\_Private\DisposableWriteHandleWrapperTrait<
    Unix\CloseableSocket,
  >;

  public function __construct(Unix\CloseableSocket $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): string {
    return $this->impl->getLocalAddress();
  }

  public function getPeerAddress(): string {
    return $this->impl->getPeerAddress();
  }
}
