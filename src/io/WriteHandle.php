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

/** An interface for a writable Handle.
 *
 * Order of operations is guaranteed, *except* for `rawWriteBlocking`;
 * `rawWriteBlocking()` will immediately try to write to the handle.
 */
interface WriteHandle extends Handle {
  /** An immediate unordered write.
   *
   * @see `genWrite()`
   * @throws `OS\BlockingIOException` if the handle is a socket or similar,
   *   and the write would block.
   * @returns the number of bytes written on success
   *
   * Returns the number of bytes written, which may be 0.
   */
  public function write(string $bytes): int;

  /** Write data, waiting if necessary.
   *
   * A wrapper around `write()` that will wait if `write()` would throw
   * an `OS\BlockingIOException`
   *
   * It is possible for the write to *partially* succeed - check the return
   * value and call again if needed.
   *
   * @returns the number of bytes written, which may be less than the length of
   *   input string.
   */
  public function writeAsync(
    string $bytes,
    ?int $timeout_ns = null,
  ): Awaitable<int>;

  public function flushAsync(): Awaitable<void>;
}
