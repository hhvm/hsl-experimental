<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO\_Private;

use namespace HH\Lib\Experimental\IO;

final class PipeWriteHandle
  extends LegacyPHPResourceHandle
  implements IO\CloseableWriteHandle {
  use LegacyPHPResourceWriteHandleTrait;

  public function __construct(resource $r) {
    parent::__construct($r);
  }
}
