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

use namespace HH\Lib\Experimental\{IO, Network, OS};
use namespace HH\Lib\Str;

function throw_socket_error(int $php_socket_error, string $message): noreturn {
  invariant($php_socket_error !== 0, "%s should not be called on success", __FUNCTION__);
  if ($php_socket_error < 0) {
    $herror = (-($php_socket_error + 100000)) as OS\_Private\HError;
    $name = 'HERROR_'.OS\_Private\HError::getNames()[$herror];
    throw new OS\Exception($name as OS\ErrorCode, $message);
  }
  OS\_Private\throw_errno($php_socket_error as OS\_Private\Errno, $message);
}
