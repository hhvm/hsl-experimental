<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\{IO, TCP};

final class DisposableTCPSocket
  extends DisposableHandleWrapper<TCP\NonDisposableSocket>
  implements
    \IAsyncDisposable,
    IO\DisposableReadWriteHandle,
    TCP\DisposableSocket {

  use DisposableReadHandleWrapperTrait<TCP\NonDisposableSocket>;
  use DisposableWriteHandleWrapperTrait<TCP\NonDisposableSocket>;

  public function __construct(TCP\NonDisposableSocket $impl) {
    parent::__construct($impl);
  }
}
