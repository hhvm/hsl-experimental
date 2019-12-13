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

use namespace HH\Lib\Experimental\{IO, Network, Unix};

final class CloseableSocket
  extends IO\_Private\LegacyPHPResourceHandle
  implements Unix\CloseableSocket, IO\CloseableReadWriteHandle {
  use IO\_Private\LegacyPHPResourceReadHandleTrait;
  use IO\_Private\LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $impl) {
    parent::__construct($impl);
  }

  public function getLocalAddress(): string {
    return Network\_Private\get_sock_name($this->impl)[0];
  }

  public function getPeerAddress(): string {
    return Network\_Private\get_peer_name($this->impl)[0];
  }
}
