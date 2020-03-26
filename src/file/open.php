<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\File;
use namespace HH\Lib\_Private\_File;

function open_read_only_nd(string $path): CloseableReadHandle {
  return new _File\CloseableReadHandle($path, 'rb');
}

function open_write_only_nd(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
): CloseableWriteHandle {
  return new _File\CloseableWriteHandle($path, $mode as string);
}

function open_read_write_nd(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
): CloseableReadWriteHandle {
  return new _File\CloseableReadWriteHandle(
    $path,
    ($mode as string).'+',
  );
}

<<__ReturnDisposable>>
function open_read_only(string $path): DisposableReadHandle {
  return new _File\DisposableFileReadHandle(open_read_only_nd($path));
}

<<__ReturnDisposable>>
function open_write_only(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
): DisposableWriteHandle {
  return new _File\DisposableFileWriteHandle(
    open_write_only_nd($path, $mode),
  );
}

<<__ReturnDisposable>>
function open_read_write(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
): DisposableReadWriteHandle {
  return new _File\DisposableFileReadWriteHandle(
    open_read_write_nd($path, $mode),
  );
}
