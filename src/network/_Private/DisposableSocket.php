<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network\_Private;

use namespace HH\Lib\Experimental\{IO, Network};

final class DisposableSocket
  extends IO\_Private\DisposableHandleWrapper<Network\NonDisposableSocket>
  implements \IAsyncDisposable, Network\DisposableSocket {

  use IO\_Private\DisposableReadHandleWrapperTrait<Network\NonDisposableSocket>;
  use IO\_Private\DisposableWriteHandleWrapperTrait<
    Network\NonDisposableSocket,
  >;

  public function __construct(Network\NonDisposableSocket $impl) {
    parent::__construct($impl);
  }
}
