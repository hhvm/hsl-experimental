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

final class NonDisposableSocket
  extends IO\_Private\LegacyPHPResourceHandle
  implements Network\NonDisposableSocket {
  use IO\_Private\LegacyPHPResourceReadHandleTrait;
  use IO\_Private\LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $impl) {
    parent::__construct($impl);
  }
}
