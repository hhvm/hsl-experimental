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

use namespace HH\Lib\{IO, Network, OS, Str};
use namespace HH\Lib\_Private\_OS;

const int PHP_HERROR_OFFSET = 10000;

function throw_socket_error(int $php_socket_error, string $message): noreturn {
  invariant($php_socket_error !== 0, "%s should not be called on success", __FUNCTION__);
  if ($php_socket_error < 0) {
    $herror = (-($php_socket_error + PHP_HERROR_OFFSET)) as OS\HErrno;
    throw new OS\HErrnoException($herror, OS\HErrno::getNames()[$herror]);
  }
  _OS\throw_errno($php_socket_error as OS\Errno, $message);
}
