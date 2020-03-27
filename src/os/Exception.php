<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\OS;

/**
 * Base class for exceptions reported by primitive native operations.
 *
 * This is used for errors that are indicated by `errno`, `herror`, or
 * similar interfaces covered by the `ErrorCode` enum.
 *
 * Subclasses exist for some specific `ErrorCode` values, such as:
 * - `ChildProcessException` (`ECHILD`)
 * - `ConnectionException` and its' subclasses, `BrokenPipeException`
 *   (`EPIPE`, `ESHUTDOWN`), `ConnectionAbortedException` (`ECONNABORTED`),
 *   `ConnectionRefusedException` (`ECONNREFUSED`), and
 *   `ConnectionResetException` (`ECONNRESET`)
 * - `AlreadyExistsException` (`EEXIST`)
 * - `NotFoundException` (`ENOENT`)
 * - `IsADirectoryException` (`EISDIR`)
 * - `IsNotADirectoryException` (`ENOTDIR`)
 * - `PermissionException` (`EACCESS`, `EPERM`)
 * - `ProcessLookupException` (`ESRCH`)
 * - `TimeoutError` (`ETIMEDOUT`)
 *
 * It is strongly recommended to catch subclasses instead of this class if a
 * suitable subclass is defined; for example:
 *
 * ```Hack
 * // ANTIPATTERN:
 * catch (OS\Exception $e) {
 *   if ($e->getErrorCode() === OS\ErrorCode::ENOENT) {
 *     do_stuff();
 *   }
 * }
 * // RECOMMENDED:
 * catch (OS\NotFoundException $_) {
 *   do_stuff();
 * }
 * ```
 *
 * If a suitable subclass is not defined, the antipattern is unavoidable.
 */
class Exception extends \Exception {
  public function __construct(private ErrorCode $errorCode, string $message) {
    parent::__construct($message);
  }

  final public function getErrorCode(): ErrorCode {
    return $this->errorCode;
  }
}
