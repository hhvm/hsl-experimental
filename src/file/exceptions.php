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

abstract class Exception
  extends IO\Exception
  implements OS\IExceptionWithErrno {

  public function __construct(private OS\Errno $errno) {
    parent::__construct();
  }

  public function getErrno(): OS\Errno {
    return $this->errno;
  }
}

final class CreateException extends Exception {}
final class OpenException extends Exception {}

/**
 * An exception thrown when a file lock was not successfully acquired.
 */
final class LockAcquisitionException extends Exception {}
