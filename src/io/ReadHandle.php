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
  /** An immediate, unordered read.
   *
   * You almost certainly don't want to call this; instead, use
   * `readAsync()` or `readLineAsync()`, which are wrappers around
   * this.
   *
   * This is likely to fail with `EWOULDBLOCK` and throw an
   * `OS\BlockingIOException`.
   *
   * @param max_bytes the maximum number of bytes to read
   *   - if `null`, an internal default will be used.
   *   - if 0, an `InvalidArgumentException` will be raised.
   * @returns
   *   - the read data on success
   *   - the empty string if the end of file is reached.
   */
  public function read(?int $max_bytes = null): string;

  /** Read from the handle, waiting for data if neccessary.
   *
   * A wrapper around `read()` that will wait for more data if there is none
   * available at present.
   *
	 * @param max_bytes the maximum number of bytes to read
	 *   - if `null`, an internal default will be used.
	 *   - if 0, an `InvalidArgumentException` will be raised.
	 * @returns
	 *   - the read data on success
	 *   - the empty string if the end of file is reached.

   */
  public function readAsync(
    ?int $max_bytes = null,
    ?int $timeout_ns = null,
  ): Awaitable<string>;
}
