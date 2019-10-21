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

use namespace HH\Lib\Experimental\{IO, Network, TCP};

final class NonDisposableTCPSocket
  extends IO\_Private\LegacyPHPResourceHandle
  implements TCP\NonDisposableSocket, IO\NonDisposableReadWriteHandle {
  use IO\_Private\LegacyPHPResourceReadHandleTrait;
  use IO\_Private\LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): (string, int) {
    return Network\_Private\get_sock_name($this->impl);
  }

  public function getPeerAddress(): (string, int) {
    return Network\_Private\get_peer_name($this->impl);
  }
}
