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

use namespace HH\Lib\Fileystem;
use namespace HH\Lib\_Private;

/** An `IO\Handle` that is readable. */
interface ReadHandle extends Handle {
  /** An immediate, unordered blocking read.
   *
   * You almost certainly don't want to call this; instead, use
   * `readAsync()` or `readLineAsync()`, which are wrappers around
   * this
   */
  public function rawReadBlocking(?int $max_bytes = null): string;

  /** Read until we reach `$max_bytes`, timeout, or the end of the file.
   *
   * By default, there is no limit to the size and no timeout, so the entire
   * file will be read; if the handle represents an open pipe, socket, or
   * similar, this means that the call will only return once the connection
   * is closed.
   *
   * If `$max_bytes` is `null`, there is no limit - this method will read until
   * end of file, or the timeout is reached.
   *
   * If `$max_bytes` is 0, the empty string will be returned.
   */
  public function readAsync(
    ?int $max_bytes = null,
    ?float $timeout_seconds = null,
  ): Awaitable<string>;

  /** Read until we reach `$max_bytes`, the end of the file, or the
   * end of the line.
   *
   * 'End of line' is platform-specific, and matches the C `fgets()`
   * function; the newline character/characters are included in the
   * return value. */
  public function readLineAsync(
    ?int $max_bytes = null,
    ?float $timeout_seconds = null,
  ): Awaitable<string>;
}
