<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_Unix;

use namespace HH\Lib\{IO, Unix};
use namespace HH\Lib\_Private\_IO;

final class DisposableSocket
  extends _IO\DisposableHandleWrapper<Unix\CloseableSocket>
  implements
    \IAsyncDisposable,
    IO\DisposableReadWriteHandle,
    Unix\DisposableSocket {

  use _IO\DisposableReadHandleWrapperTrait<
    Unix\CloseableSocket,
  >;
  use _IO\DisposableWriteHandleWrapperTrait<
    Unix\CloseableSocket,
  >;

  public function __construct(Unix\CloseableSocket $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): ?string {
    return $this->impl->getLocalAddress();
  }

  public function getPeerAddress(): ?string {
    return $this->impl->getPeerAddress();
  }
}
