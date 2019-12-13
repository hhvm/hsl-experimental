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
 *
 * You probably want to start with one of:
 * - `File\open_read_only()`, `File\open_write_only()`, or
 *   `File\open_read_write()`
 * - `IO\pipe()`
 * - `IO\request_input()`, `IO\request_input()`, or `IO\request_error()`; these
 *   used for all kinds of requests, including both HTTP and CLI requests.
 * - `IO\server_output()`, `IO\server_error()`
 * - `TCP\connect_async()` or `TCP\Server`
 * - `Unix\connect_async()`, or `Unix\Server`
 * - the `_nd()` or `_nd_async()` variants of the above functions if a
 *   non-disposable is required.
 *
 * All concrete instances of `IO\Handle`s` should either have a managed
 * lifecycle, or be instances of `IO\CloseableHandle`, and explicitly
 * closed by code using it.
 *
 * Handles with a managed lifecycle can not be closed manually, and take
 * several forms; for example:
 * - Disposable handles will be closed at the end of their scope.
 *   Socket peers may close a connection before this point.
 * - `IO\request_*` handles are open until the end of the request; an
 *   attached process may close them earlier - e.g. a user may close
 *   `IO\request_input()` on a CLI process by typing Ctrl+D into their shell.
 * - `IO\server_output()` is open for the lifetime of the HHVM process, unless
 *   closed by an attached process, if any.
 */
interface Handle {
  public function isEndOfFile(): bool;
}
