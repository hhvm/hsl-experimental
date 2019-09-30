<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File;

use namespace HH\Lib\{_Private, Experimental\IO};

<<__Sealed(
  _Private\DisposableFileHandle::class,
  _Private\NonDisposableFileHandle::class,
  ReadHandle::class,
  WriteHandle::class,
)>>
interface Handle extends IO\Handle {
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
  public function lock(LockType $mode): Lock;
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
interface ReadWriteHandle
  extends WriteHandle, ReadHandle, IO\ReadWriteHandle {
}
