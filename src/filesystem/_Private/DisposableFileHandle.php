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

<<Sealed(TemporaryFile::class)>>
class DisposableFileHandle
  extends DisposableHandleWrapper<FileHandle>
  implements Filesystem\DisposableFileReadWriteHandle {

  public function __construct(FileHandle $impl) {
    parent::__construct($impl);
  }

  final public function getPath(): Filesystem\Path {
    return $this->impl->getPath();
  }

  final public function getSize(): int {
    return $this->impl->getSize();
  }

  final public function seekRaw(int $offset): void {
    $this->impl->seekRaw($offset);
  }

  final public async function seekAsync(int $offset): Awaitable<void> {
    await $this->impl->seekAsync($offset);
  }

  final public function seekBlocking(int $offset): void {
    $this->impl->seekBlocking($offset);
  }
}
