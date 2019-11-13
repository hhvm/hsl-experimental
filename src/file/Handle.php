<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File;

use namespace HH\Lib\Experimental\IO;

<<__Sealed(
  _Private\DisposableFileHandle::class,
  _Private\NonDisposableFileHandle::class,
  ReadHandle::class,
  WriteHandle::class,
)>>
interface Handle extends IO\SeekableHandle {
  /**
   * Get the name of this file.
   */
  public function getPath(): Path;

  /**
   * Get the size of the file.
   */
  public function getSize(): int;

  /**
   * Get a shared or exclusive lock on the file.
   *
   * This will block until it acquires the lock, which may be forever.
   *
   * This involves a blocking syscall; async code will not execute while
   * waiting for a lock.
   */
  <<__ReturnDisposable>>
  public function lock(LockType $mode): Lock;

  /**
   * Immediately get a shared or exclusive lock on a file, or throw.
   *
   * @throws `File\AlreadyLockedException` if `lock()` would block. **This
   *   is not a subclass of `OS\Exception`**.
   * @throws `OS\Exception` in any other case.
   */
  <<__ReturnDisposable>>
  public function tryLockx(LockType $mode): Lock;
}

<<__Sealed(
  DisposableReadHandle::class,
  ReadWriteHandle::class,
  NonDisposableReadHandle::class,
)>>
interface ReadHandle extends Handle, IO\ReadHandle {
}

<<__Sealed(
  DisposableWriteHandle::class,
  ReadWriteHandle::class,
  NonDisposableWriteHandle::class,
)>>
interface WriteHandle extends Handle, IO\WriteHandle {
}

<<__Sealed(
  NonDisposableReadWriteHandle::class,
  DisposableReadWriteHandle::class,
)>>
interface ReadWriteHandle extends WriteHandle, ReadHandle, IO\ReadWriteHandle {
}
