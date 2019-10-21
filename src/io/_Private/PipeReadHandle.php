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

final class PipeReadHandle
  extends LegacyPHPResourceHandle
  implements IO\NonDisposableReadHandle {
  use LegacyPHPResourceReadHandleTrait;
  public function __construct(resource $r) {
    parent::__construct($r);
  }
}
