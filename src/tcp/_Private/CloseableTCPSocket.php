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

use namespace HH\Lib\{IO, Network, TCP};
use namespace HH\Lib\_Private\{_IO, _Network};

final class CloseableTCPSocket
  extends _IO\LegacyPHPResourceHandle
  implements TCP\CloseableSocket, IO\CloseableReadWriteHandle {
  use _IO\LegacyPHPResourceReadHandleTrait;
  use _IO\LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): (string, int) {
    return _Network\get_sock_name($this->impl);
  }

  public function getPeerAddress(): (string, int) {
    return _Network\get_peer_name($this->impl);
  }
}
