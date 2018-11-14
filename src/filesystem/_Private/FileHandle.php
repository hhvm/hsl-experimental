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

final class FileHandle
  extends NativeHandle
  implements Filesystem\FileReadHandle, Filesystem\FileWriteHandle {

  public function __construct(
    private string $filename,
    private resource $impl,
  ) {
    parent::__construct($impl);
  }

  /** DO NOT USE THIS */
  final public function getImplementationDetail(): resource {
    return $this->impl;
  }

  <<__Memoize>>
  final public function getPath(): Filesystem\Path {
    return new Filesystem\Path($this->filename);
  }

  final public function getSize(): int {
    return \filesize($this->filename);
  }

  <<__ReturnDisposable>>
  final public function lock(
    Filesystem\FileLockType $type,
  ): Filesystem\FileLock {
    return new Filesystem\FileLock($this, $type);
  }

  final private function seekRaw(int $offset): void {
    \fseek($this->impl, $offset);
  }

  // FileWriteHandle

  final public async function seekForWriteAsync(int $offset): Awaitable<void> {
    await $this->queuedAsync(async () ==> $this->seekRaw($offset));
  }

  // FileReadHandle

  final public function seekForRead(int $offset): void {
    $this->seekRaw($offset);
  }
}
