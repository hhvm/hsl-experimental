<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\Experimental\{IO, OS};

abstract class Exception extends IO\Exception {
  abstract public function getHErrno(): ?OS\HErrno;
}

/**
 * Exception thrown if a host name could not be resolved to an
 * address usable for the socket type.
 *
 * You may also want to catch `SocketException`, or the base
 * `Network\Exception`.
 */
final class HostResolutionException extends Exception {
  public function __construct(private OS\HErrno $herrno) {
    parent::__construct();
  }

  public function getErrno(): null  {
    return null;
  }

  public function getHErrno(): OS\HErrno {
    return $this->herrno;
  }
}

/**
 * Class for exceptions in socket calls.
 *
 * Sites that catch this likely want to also catch `HostResolutionException`, or
 * just `Network\Exception`.
 */
class SocketException extends Exception implements OS\IExceptionWithErrno {
  public function __construct(private OS\Errno $errno) {
    parent::__construct();
  }

  public function getErrno(): OS\Errno {
    return $this->errno;
  }

  public function getHErrno(): null {
    return null;
  }
}

final class AddressNotAvailableException extends SocketException {
  public function __construct() {
    parent::__construct(OS\Errno::EADDRNOTAVAIL);
  }
}
