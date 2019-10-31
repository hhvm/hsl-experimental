<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\OS\_Private;

use namespace HH\Lib\Experimental\OS;

const bool IS_MACOS = \PHP_OS === 'Darwin';

function errno(): ?Errno {
  /* HH_IGNORE_ERROR[2049] PHPStdLib */
  /* HH_IGNORE_ERROR[4107] PHPStdLib */
  $errno = \posix_get_last_error() as int;
  return $errno === 0 ? null : Errno::assert($errno);
}
