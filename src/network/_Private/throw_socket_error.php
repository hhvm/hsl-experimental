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

function throw_socket_error(string $_operation, int $errno): noreturn {
  invariant($errno !== 0, "%s should not be called on success", __FUNCTION__);
  if ($errno < 0) {
    throw new Network\HostResolutionException((-($errno + 10000)) as OS\HErrno);
  }
  switch ($errno as OS\Errno) {
    case OS\Errno::EADDRNOTAVAIL:
      throw new Network\AddressNotAvailableException();
    default:
      throw new IO\_Private\UnhandledOSErrnoException($errno as OS\Errno);
  }
}
