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

use function HH\Lib\_Private\fopen;

function open_read_only(string $path): FileReadHandle {
  return fopen($path, 'r');
}

function open_write_only(
  string $path,
  FileWriteMode $mode = FileWriteMode::OPEN_OR_CREATE,
): FileWriteHandle {
  return fopen($path, $mode as string);
}

function open_read_write(
  string $path,
  FileReadWriteMode $mode = FileReadWriteMode::OPEN_EXISTING,
): FileReadWriteHandle {
  return fopen($path, $mode as string);
}
