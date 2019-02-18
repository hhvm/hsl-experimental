<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

use namespace HH\Lib\Experimental\Filesystem;

/** An interface for IO handles.
 *
 * Order of operations is guaranteed.
 */
<<__Sealed(
  Filesystem\FileHandle::class,
  UserspaceHandle::class,
  ReadHandle::class,
  WriteHandle::class,
  DuplexHandle::class,
)>>
interface Handle {
  public function isEndOfFile(): bool;
  public function closeAsync(): Awaitable<void>;
}
