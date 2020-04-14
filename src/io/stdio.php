<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\IO;

use namespace HH\Lib\OS;
use namespace HH\Lib\_Private\{_IO, _OS};

/** Return STDOUT for the server process.
 *
 * This is usually not the same thing as request output.
 *
 * @see request_output
 */
<<__Memoize>>
function server_output(): WriteHandle {
  return new _IO\StdioWriteHandle('php://stdout');
}

/** Return STDERR for the server process.
 *
 * @see request_error
 */
<<__Memoize>>
function server_error(): WriteHandle {
  return new _IO\StdioWriteHandle('php://stderr');
}

/** Return the output handle for the current request.
 *
 * This should generally be used for sending data to clients. In CLI mode, this
 * is usually the process STDOUT.
 *
 * @see requestOutput
 */
<<__Memoize>>
function request_output(): CloseableWriteHandle {
  // php://output has differing eof behavior for interactive stdin - we need
  // the php://stdout for interactive usage (e.g. repls)
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  if (\php_sapi_name() === "cli") {
    return server_output() as CloseableWriteHandle;
  }
  return new _IO\StdioWriteHandle('php://output');
}

/** Return the error output handle for the current request.
 *
 * This is usually only available for CLI scripts; it will return null in most
 * other contexts, including HTTP requests.
 *
 * For a throwing version, use `request_error(x)`.
 *
 * In CLI mode, this is usually the process STDERR.
 */
function request_error(): ?CloseableWriteHandle {
  try {
    return request_errorx();
  } catch (OS\NotFoundException $_) {
    return null;
  }
}

/** Return the error output handle for the current request.
 *
 * This is usually only available for CLI scripts; it will throw an
 * `NotFoundException` in most other contexts, including HTTP
 * requests.
 *
 * For a non-throwing version, use `request_error()`.
 *
 * In CLI mode, this is usually the process STDERR.
 */
function request_errorx(): CloseableWriteHandle {
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  if (\php_sapi_name() !== "cli") {
    _OS\throw_errno(
      OS\Errno::ENOENT,
      "There is no request_error() handle",
    );
  }
  return server_error() as CloseableWriteHandle;
}

/** Return the input handle for the current request.
 *
 * In CLI mode, this is likely STDIN; for HTTP requests, it may contain the
 * POST data, if any.
 */
<<__Memoize>>
function request_input(): CloseableReadHandle {
  // php://input has differing eof behavior for interactive stdin - we need
  // the php://stdin for interactive usage (e.g. repls)
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  if (\php_sapi_name() === "cli") {
    return new _IO\StdioReadHandle('php://stdin');
  }
  return new _IO\StdioReadHandle('php://input');
}
