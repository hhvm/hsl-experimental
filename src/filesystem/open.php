<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Filesystem;

use namespace HH\Lib\_Private;

function open_read_only_non_disposable(string $path): FileReadHandle {
  return _Private\fopen($path, 'rb');
}

function open_write_only_non_disposable(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): FileWriteHandle {
  return _Private\fopen($path, $mode as string);
}

<<__ReturnDisposable>>
function open_read_only(string $path): DisposableFileReadHandle {
  return new _Private\DisposableFileHandle(
    open_read_only_non_disposable($path) as _Private\FileHandle,
  );
}

<<__ReturnDisposable>>
function open_write_only(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): DisposableFileWriteHandle {
  return new _Private\DisposableFileHandle(
    open_write_only_non_disposable($path, $mode) as _Private\FileHandle,
  );
}
