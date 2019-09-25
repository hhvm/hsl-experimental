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

function open_read_only_non_disposable(
  string $path,
): NonDisposableFileReadHandle {
  return
    NonDisposableFileReadHandle::__createInstance_IMPLEMENTATION_DETAIL_DO_NOT_USE(
      $path,
      'rb',
    );
}

function open_write_only_non_disposable(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): NonDisposableFileWriteHandle {
  return
    NonDisposableFileWriteHandle::__createInstance_IMPLEMENTATION_DETAIL_DO_NOT_USE(
      $path,
      $mode as string,
    );
}

function open_read_write_non_disposable(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): NonDisposableFileReadWriteHandle {
  return
    NonDisposableFileReadWriteHandle::__createInstance_IMPLEMENTATION_DETAIL_DO_NOT_USE(
      $path,
      ($mode as string).'+',
    );
}

<<__ReturnDisposable>>
function open_read_only(string $path): DisposableFileReadHandle {
  return new _Private\DisposableFileReadHandle(
    open_read_only_non_disposable($path),
  );
}

<<__ReturnDisposable>>
function open_write_only(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): DisposableFileWriteHandle {
  return new _Private\DisposableFileWriteHandle(
    open_write_only_non_disposable($path, $mode),
  );
}

<<__ReturnDisposable>>
function open_read_write(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): DisposableFileReadWriteHandle {
  return new _Private\DisposableFileReadWriteHandle(
    open_read_write_non_disposable($path, $mode),
  );
}
