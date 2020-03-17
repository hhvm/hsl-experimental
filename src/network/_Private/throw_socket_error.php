<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_Network;

use namespace HH\Lib\Experimental\{IO, Network, OS};
use namespace HH\Lib\_Private\_OS;
use namespace HH\Lib\Str;

const int PHP_HERROR_OFFSET = 10000;

function throw_socket_error(int $php_socket_error, string $message): noreturn {
  invariant($php_socket_error !== 0, "%s should not be called on success", __FUNCTION__);
  if ($php_socket_error < 0) {
    $herror = (-($php_socket_error + PHP_HERROR_OFFSET)) as _OS\HError;
    $name = 'HERROR_'._OS\HError::getNames()[$herror];
    throw new OS\Exception($name as OS\ErrorCode, $message);
  }
  _OS\throw_errno($php_socket_error as _OS\Errno, $message);
}
