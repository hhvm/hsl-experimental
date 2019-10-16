<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\Network;

final class NonDisposableSocket
  extends LegacyPHPResourceHandle
  implements Network\NonDisposableSocket {
  use LegacyPHPResourceReadHandleTrait;
  use LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $impl) {
    parent::__construct($impl);
  }
}
