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

final class StdioReadHandle
  extends LegacyPHPResourceHandle
  implements IO\NonDisposableReadHandle {
  use LegacyPHPResourceReadHandleTrait;

  public function __construct(string $php_uri) {
    /* HH_IGNORE_ERROR[2049] PHP stdlib */
    /* HH_IGNORE_ERROR[4107] PHP stdlib */
    parent::__construct(\fopen($php_uri, 'r'));
  }
}
