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

use namespace HH\Lib\Experimental\Filesystem;

/** An interface for a writable Handle.
 *
 * Order of operations is guaranteed, *except* for `rawWriteBlocking`;
 * `rawWriteBlocking()` will immediately try to write to the handle.
 *
 * All other methods will wait for any pending operations to complete, which
 * usually involves a hidden `\HH\Asio\join()`.
 */
<<__Sealed(
  ReadWriteHandle::class,
  UserspaceHandle::class,
  Filesystem\FileWriteHandle::class,
  DisposableWriteHandle::class,
)>>
interface WriteHandle extends Handle {

  /** Possibly write some of the string.
   *
   * Returns the number of bytes written, which may be 0.
   */
  public function rawWriteBlocking(string $bytes): int;

  public function writeAsync(string $bytes): Awaitable<void>;
  public function writeBlocking(string $bytes): void;

  public function flushAsync(): Awaitable<void>;
  public function flushBlocking(): void;
}
