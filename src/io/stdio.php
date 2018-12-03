<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

use namespace HH\Lib\_Private;

/** Return STDOUT for the server process.
 *
 * This is usually not the same thing as request output.
 *
 * @see request_output
 */
function server_output(): WriteHandle {
  return _Private\StdioHandle::serverOutput();
}

/** Return STDERR for the server process.
 *
 * @see request_error
 */
function server_error(): WriteHandle {
  return _Private\StdioHandle::serverError();
}

/** Return STDIN for the server process.
 *
 * @see request_input
 */
function server_input(): ReadHandle {
  return _Private\StdioHandle::serverInput();
}

/** Return the output handle for the current request.
 *
 * This should generally be used for sending data to clients. In CLI mode, this
 * is usually the process STDOUT.
 *
 * @see requestOutput
 */
function request_output(): WriteHandle {
  return _Private\StdioHandle::requestOutput();
}

/** Return the error output handle for the current request.
 *
 * This is usually only available for CLI scripts; it will throw an
 * `UnsupportedHandleException` in most other contexts, including HTTP
 * requests.
 *
 * In CLI mode, this is usually the process STDERR.
 */
function request_error(): WriteHandle {
  return _Private\StdioHandle::requestError();
}

/** Return the input handle for the current request.
 *
 * In CLI mode, this is likely STDIN; for HTTP requests, it may contain the
 * POST data, if any.
 */
function request_input(): ReadHandle {
  return _Private\StdioHandle::requestInput();
}
