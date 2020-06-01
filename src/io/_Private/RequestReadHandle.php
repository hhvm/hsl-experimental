<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_IO;

use namespace HH\Lib\{IO, OS};

final class RequestReadHandle
  extends LegacyPHPResourceHandle
  implements IO\CloseableReadHandle {
  use LegacyPHPResourceReadHandleTrait;

  public function __construct() {
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    parent::__construct(\fopen('php://input', 'r'));
  }
}
