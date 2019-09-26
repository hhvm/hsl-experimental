<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Filesystem;

use namespace HH\Lib\{_Private, Experimental\IO};

<<__Sealed(
  FileReadHandle::class,
  FileWriteHandle::class,
)>>
interface FileHandle extends IO\Handle {
  /**
   * Get the name of this file.
   */
  public function getPath(): Path;

  /**
   * Get the size of the file.
   */
  public function getSize(): int;

  <<__ReturnDisposable>>
  public function lock(FileLockType $mode): FileLock;
}

<<__Sealed(DisposableFileReadHandle::class, FileReadWriteHandle::class)>>
interface FileReadHandle extends FileHandle, IO\ReadHandle {
  public function seekForRead(int $offset): void;
}

<<__Sealed(DisposableFileWriteHandle::class, FileReadWriteHandle::class)>>
interface FileWriteHandle extends FileHandle, IO\WriteHandle {
  /**
   * Move to a specific offset within a file.
   *
   * Offset is relative to the start of the file - so, the beginning of the
   * file is always offset 0.
   *
   * Any other pending operations (such as writes) will complete first.
   */
  public function seekForWriteAsync(int $offset): Awaitable<void>;
}

<<__Sealed(_Private\FileHandle::class, DisposableFileReadWriteHandle::class)>>
interface FileReadWriteHandle
  extends FileWriteHandle, FileReadHandle, IO\ReadWriteHandle {
}

<<__Sealed(DisposableFileReadWriteHandle::class)>>
interface DisposableFileReadHandle
  /* HH_FIXME[4194] non-disposable parent interface t34965102 */
  extends FileReadHandle, IO\DisposableReadHandle {
}

<<__Sealed(DisposableFileReadWriteHandle::class)>>
interface DisposableFileWriteHandle
  /* HH_FIXME[4194] non-disposable parent interface t34965102 */
  extends FileWriteHandle, IO\DisposableWriteHandle {
}

<<__Sealed(_Private\DisposableFileHandle::class)>>
interface DisposableFileReadWriteHandle
  extends
    /* HH_FIXME[4194] non-disposable parent interface t34965102 */
    FileReadWriteHandle,
    DisposableFileWriteHandle,
    DisposableFileReadHandle,
    IO\DisposableReadWriteHandle {
}
