<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\OS;

use namespace HH\Lib\C;

final class ChildProcessException extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ECHILD;
  }
}

abstract class ConnectionException extends Exception {
}

final class BrokenPipeException extends ConnectionException {
  use _Private\ExceptionWithMultipleErrorCodesTrait;

  <<__Override>>
  public static function _getValidErrorCodes(): keyset<ErrorCode> {
    return keyset[ErrorCode::EPIPE, ErrorCode::ESHUTDOWN];
  }
}

final class ConnectionAbortedException extends ConnectionException {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ECONNABORTED;
  }
}

final class ConnectionRefusedException extends ConnectionException {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ECONNREFUSED;
  }
}

final class ConnectionResetException extends ConnectionException {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ECONNRESET;
  }
}

final class AlreadyExistsException extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::EEXIST;
  }
}

final class NotFoundException extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ENOENT;
  }
}

final class IsADirectoryException extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::EISDIR;
  }
}

final class IsNotADirectoryException extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ENOTDIR;
  }
}

final class PermissionException extends Exception {
  use _Private\ExceptionWithMultipleErrorCodesTrait;

  <<__Override>>
  public static function _getValidErrorCodes(): keyset<ErrorCode> {
    return keyset[
      ErrorCode::EACCES,
      ErrorCode::EPERM,
    ];
  }
}

final class ProcessLookupException extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ESRCH;
  }
}

final class TimeoutError extends Exception {
  use _Private\ExceptionWithSingleErrorCodeTrait;

  <<__Override>>
  public static function _getValidErrorCode(): ErrorCode {
    return ErrorCode::ETIMEDOUT;
  }
}
