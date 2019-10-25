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

/**
 * Exception thrown if a host name could not be resolved to an
 * address usable for the socket type.
 *
 * You may also want to catch `SocketException`, or the base
 * `Network\Exception`.
 */
final class HostResolutionException extends IO\Exception {
  public function __construct(private OS\HErrno $herrno) {
    parent::__construct();
  }

  public function getErrno(): null {
    return null;
  }

  public function getHErrno(): OS\HErrno {
    return $this->herrno;
  }
}

final class AddressNotAvailableException
  extends IO\Exception
  implements IO\ExceptionWithErrno {
  public function getErrno(): OS\Errno {
    return OS\Errno::EADDRNOTAVAIL;
  }
}
