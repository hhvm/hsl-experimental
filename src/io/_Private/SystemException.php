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

use namespace HH\Lib\Experimental\{IO, OS};

/** Class for Errnos without a more-specific exception.
 *
 * DO NOT CATCH THIS DIRECTLY. Catch `IO\Exception` or `IO\ExceptionWithErrno`
 * instead.
 */
final class SystemException
  extends IO\Exception
  implements IO\ExceptionWithErrno {
  use OS\_Private\ExceptionWithErrnoTrait<OS\Errno>;
}
