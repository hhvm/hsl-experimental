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

<<__Sealed(FileReadHandle::class, FileWriteHandle::class)>>
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

  /**
   * Blocking form of `seekAsync`, which should be preferred.
   */
  public function seekBlocking(int $offset): void;

  /** Blocking form of `seekAsync` which does not wait for any other pending
   * operations to finish. */
  public function seekRaw(int $offset): void;
}

<<__Sealed(FileReadWriteHandle::class)>>
interface FileReadHandle extends FileHandle, IO\ReadHandle {
}

<<__Sealed(FileReadWriteHandle::class)>>
interface FileWriteHandle extends FileHandle, IO\WriteHandle {
}

<<__Sealed(_Private\FileHandle::class)>>
interface FileReadWriteHandle
  extends FileReadHandle, FileWriteHandle, IO\ReadWriteHandle {
}
