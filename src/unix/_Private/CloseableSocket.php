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

use namespace HH\Lib\{IO, Network, Unix};
use namespace HH\Lib\_Private\{_IO, _Network};

final class CloseableSocket
  extends _IO\LegacyPHPResourceHandle
  implements Unix\CloseableSocket, IO\CloseableReadWriteHandle {
  use _IO\LegacyPHPResourceReadHandleTrait;
  use _IO\LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): string {
    return _Network\get_sock_name($this->impl)[0];
  }

  public function getPeerAddress(): string {
    return _Network\get_peer_name($this->impl)[0];
  }
}
