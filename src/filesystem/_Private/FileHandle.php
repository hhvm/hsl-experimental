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
  implements Filesystem\FileReadWriteHandle {

  public function __construct(private string $filename, private resource $impl) {
    parent::__construct($impl);
  }

  <<__Memoize>>
  final public function getPath(): Filesystem\Path {
    return new Filesystem\Path($this->filename);
  }

  final public function getSize(): int {
    return \filesize($this->filename);
  }

  final public function seekRaw(int $offset): void {
    \fseek($this->impl, $offset);
  }

  final public async function seekAsync(int $offset): Awaitable<void> {
    await $this->queuedAsync(async () ==> $this->seekRaw($offset));
  }

  final public function seekBlocking(int $offset): void {
    \HH\Asio\join($this->seekAsync($offset));
  }
}
