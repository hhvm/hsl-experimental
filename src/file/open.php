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
use namespace HH\Lib\{OS, _Private\_File};

function open_read_only_nd(string $path): CloseableReadHandle {
  return OS\open($path, OS\O_RDONLY)
    |> new _File\CloseableReadHandle($$, $path);
}

function open_write_only_nd(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
  int $create_file_permissions = 0644,
): CloseableWriteHandle {
  return OS\open(
    $path,
    OS\O_WRONLY | $mode as int,
    ($mode & OS\O_CREAT) ? $create_file_permissions : null,
  )
    |> new _File\CloseableWriteHandle($$, $path);
}

function open_read_write_nd(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
  int $create_file_permissions = 0644,
): CloseableReadWriteHandle {
  return OS\open(
    $path,
    OS\O_RDWR | $mode as int,
    ($mode & OS\O_CREAT) ? $create_file_permissions : null,
  )
    |> new _File\CloseableReadWriteHandle($$, $path);
}

<<__ReturnDisposable>>
function open_read_only(string $path): DisposableReadHandle {
  return new _File\DisposableFileReadHandle(open_read_only_nd($path));
}

<<__ReturnDisposable>>
function open_write_only(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
  int $create_file_permissions = 0644,
): DisposableWriteHandle {
  return new _File\DisposableFileWriteHandle(
    open_write_only_nd($path, $mode, $create_file_permissions),
  );
}

<<__ReturnDisposable>>
function open_read_write(
  string $path,
  WriteMode $mode = WriteMode::OPEN_OR_CREATE,
  int $create_file_permissions = 0644,
): DisposableReadWriteHandle {
  return new _File\DisposableFileReadWriteHandle(
    open_read_write_nd($path, $mode, $create_file_permissions),
  );
}
