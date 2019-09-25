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
  _Private\DisposableFileHandle::class,
  _Private\NonDisposableFileHandle::class,
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

  /**
   * Move to a specific offset within a file.
   *
   * Offset is relative to the start of the file - so, the beginning of the
   * file is always offset 0.
   *
   * Any other pending operations (such as writes) will complete first.
   */
  public function seekAsync(int $offset): Awaitable<void>;

  <<__ReturnDisposable>>
  public function lock(FileLockType $mode): FileLock;
}

<<__Sealed(
  DisposableFileReadHandle::class,
  FileReadWriteHandle::class,
  NonDisposableFileReadHandle::class,
)>>
interface FileReadHandle extends FileHandle, IO\ReadHandle {
}

<<__Sealed(
  DisposableFileWriteHandle::class,
  FileReadWriteHandle::class,
  NonDisposableFileWriteHandle::class,
)>>
interface FileWriteHandle extends FileHandle, IO\WriteHandle {
}

<<__Sealed(
  NonDisposableFileReadWriteHandle::class,
  DisposableFileReadWriteHandle::class,
)>>
interface FileReadWriteHandle
  extends FileWriteHandle, FileReadHandle, IO\ReadWriteHandle {
}
