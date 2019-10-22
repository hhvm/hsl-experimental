<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File;

use namespace HH\Lib\Experimental\{IO, OS};

/**
 * An exception thrown when opening a file fails.
 */
final class OpenException
  extends IO\Exception
  implements IO\ExceptionWithErrno {
  use OS\_Private\ExceptionWithErrnoTrait<OS\Errno>;
}

/**
 * An exception thrown when a file lock was not successfully acquired.
 */
final class LockAcquisitionException
  extends IO\Exception
  implements IO\ExceptionWithErrno {
  use OS\_Private\ExceptionWithErrnoTrait<OS\Errno>;
}
