<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

use namespace HH\Lib\Experimental\{File, Network};

/** An interface for an IO stream.
 *
 * For example, an IO handle might be attached to a file, a network socket, or
 * just an in-memory buffer.
 *
 * HSL IO handles can be thought of as having a combination of behaviors - some
 * of which are mutually exclusive - which are reflected in more-specific
 * interfaces; for example:
 * - Disposable, Closeable, or neither
 * - Seekable
 * - Readable
 * - Writable
 *
 * `IO\DisposableHandle`s are automatically closed at the end of the `using`
 * scope, but do not expose an explicit close method; `IO\CloseableHandle`s are
 * never disposable, but do provide an explicit close method. Some special
 * handles are neither closeable or disposable, such as the handle returned by
 * `IO\server_error()`.
 *
 * These can be combined to arbitrary interfaces; for example, if you are
 * writing a function that writes some data, you may want to take a
 * `<<__AcceptDisposable>> IO\WriteHandle` - or, if you read, write, and seek,
 * `<<__AcceptDisposable>> IO\SeekableReadWriteHandle`. If you need to store a
 * handle, remove the `<<__AcceptDisposable>>`; only specify `Closeable` if
 * your code requires that the close method is defined.
 *
 * Some types of handle imply these behaviors; for example, all `File\Handle`s
 * are `IO\SeekableHandle`s.
 */
<<__Sealed(
  File\Handle::class,
  Network\Socket::class,
  CloseableHandle::class,
  ReadHandle::class,
  UserspaceHandle::class,
  SeekableHandle::class,
  WriteHandle::class,
  _Private\DisposableHandleWrapper::class,
)>>
interface Handle {
  public function isEndOfFile(): bool;
}
