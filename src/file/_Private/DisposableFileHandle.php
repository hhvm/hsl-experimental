<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File\_Private;

use namespace HH\Lib\Experimental\{File, IO};

<<__ConsistentConstruct>>
abstract class DisposableFileHandle<T as File\CloseableHandle>
  extends IO\_Private\DisposableHandleWrapper<T>
  implements File\Handle {
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
  final public function lock(File\LockType $type): File\Lock {
    return $this->impl->lock($type);
  }

  <<__ReturnDisposable>>
  final public function tryLockx(File\LockType $type): File\Lock {
    return $this->impl->tryLockx($type);
  }

  final public async function seekAsync(int $offset): Awaitable<void> {
    await $this->impl->seekAsync($offset);
  }

  final public function tell(): int {
    return $this->impl->tell();
  }
}
