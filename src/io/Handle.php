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

use namespace HH\Lib\_Private;
use namespace HH\Lib\Experimental\File;

/** An interface for IO handles.
 *
 * Order of operations is guaranteed.
 */
<<__Sealed(
  File\FileHandle::class,
  NonDisposableHandle::class,
  ReadHandle::class,
  UserspaceHandle::class,
  WriteHandle::class,
  _Private\DisposableHandleWrapper::class,
)>>
interface Handle {
  public function isEndOfFile(): bool;
}
