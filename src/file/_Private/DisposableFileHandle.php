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

use namespace HH\Lib\Experimental\File;

<<__ConsistentConstruct>>
abstract class DisposableFileHandle<T as NonDisposableFileHandle>
  extends DisposableHandleWrapper<T>
  implements File\FileHandle {
  final public function __construct(T $impl) {
    parent::__construct($impl);
  }

  final public function getPath(): File\Path {
    return $this->impl->getPath();
  }

  final public function getSize(): int {
    return $this->impl->getSize();
  }

  <<__ReturnDisposable>>
  final public function lock(
    File\FileLockType $type,
  ): File\FileLock {
    return $this->impl->lock($type);
  }

  final public async function seekAsync(int $offset): Awaitable<void> {
    await $this->impl->seekAsync($offset);
  }
}
