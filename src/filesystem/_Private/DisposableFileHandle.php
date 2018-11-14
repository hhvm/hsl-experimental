<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\Filesystem;

<<__Sealed(TemporaryFile::class)>>
class DisposableFileHandle
  extends DisposableHandleWrapper<FileHandle>
  implements
    Filesystem\DisposableFileReadHandle,
    Filesystem\DisposableFileWriteHandle {

  public function __construct(FileHandle $impl) {
    parent::__construct($impl);
  }

  final public function getPath(): Filesystem\Path {
    return $this->impl->getPath();
  }

  final public function getSize(): int {
    return $this->impl->getSize();
  }

  <<__ReturnDisposable>>
  final public function lock(
    Filesystem\FileLockType $type,
  ): Filesystem\FileLock {
    return $this->impl->lock($type);
  }

  final public async function seekForWriteAsync(int $offset): Awaitable<void> {
    await $this->impl->seekForWriteAsync($offset);
  }

  final public function seekForRead(int $offset): void {
    $this->impl->seekForRead($offset);
  }
}
