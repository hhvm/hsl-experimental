<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\OS;

// todo: extend for common cases; consider sealing vs leaving fully open.
final class Exception extends \Exception {
  public function __construct(
    private ErrorCode $errorCode,
    string $message,
  ) {
    parent::__construct($message);
  }

  final public function getErrorCode(): ErrorCode {
    return $this->errorCode;
  }
}
