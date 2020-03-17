<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_OS;

use namespace HH\Lib\C;
use namespace HH\Lib\Experimental\OS;

trait ExceptionWithMultipleErrorCodesTrait {
  require extends OS\Exception;

  final public function __construct(OS\ErrorCode $code, string $message) {
    invariant(
      C\contains(static::_getValidErrorCodes(), $code),
      'Exception %s constructed with invalid code %s',
      static::class,
      $code,
    );
    parent::__construct($code, $message);
  }

  abstract public static function _getValidErrorCodes(): keyset<OS\ErrorCode>;
}

trait ExceptionWithSingleErrorCodeTrait {
  require extends OS\Exception;

  final public function __construct(string $message) {
    parent::__construct(static::_getValidErrorCode(), $message);
  }

  abstract public static function _getValidErrorCode(): OS\ErrorCode;
}
