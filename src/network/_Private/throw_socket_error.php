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
use namespace HH\Lib\Str;

function throw_socket_error(string $operation, int $errno): noreturn {
  invariant($errno !== 0, "%s should not be called on success", __FUNCTION__);
  // TODO: throw more specific errors, depending on if `$errno` is:
  // - a specific value
  // - a positive value (OS\Errno)
  // - a negative value -(OS\HErrno + 10000)
  throw new \Exception(
    Str\format(
      "Error %s: %s",
      $operation,
      /* HH_IGNORE_ERROR[2049] PHPStdLib */
      /* HH_IGNORE_ERROR[4107] PHPStdLib */
      \socket_strerror($errno),
    ),
  );
}
