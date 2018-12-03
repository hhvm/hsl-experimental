<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\IO;

final class StdioHandle extends NativeHandle {
  <<__Memoize>>
  public static function serverError(): IO\WriteHandle {
    // while the documentation says to use the STDERR constant, that is
    // conditionally defined
    return new self(\fopen('php://stderr', 'w'));
  }

  <<__Memoize>>
  public static function serverOutput(): IO\WriteHandle {
    return new self(\fopen('php://stdout', 'w'));
  }

  <<__Memoize>>
  public static function serverInput(): IO\ReadHandle {
    return new self(\fopen('php://stdin', 'r'));
  }

  <<__Memoize>>
  public static function requestInput(): IO\ReadHandle {
    return new self(\fopen('php://input', 'r'));
  }

  <<__Memoize>>
  public static function requestOutput(): IO\WriteHandle{
    return new self(\fopen('php://output', 'w'));
  }

  <<__Memoize>>
  public static function requestError(): IO\WriteHandle {
    /* HH_FIXME[2049] deregistered PHP stdlib */
    /* HH_FIXME[4107] deregistered PHP stdlib */
    if (\php_sapi_name() !== "cli") {
      throw new IO\InvalidHandleException(
        "requestError is not supported in the current execution mode"
      );
    }
    return self::serverError();
  }
}
