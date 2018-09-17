<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\Filesystem;

final class FileHandle
  extends NativeHandle
  implements Filesystem\FileReadWriteHandle {

  public function __construct(private string $filename, resource $impl) {
    parent::__construct($impl);
  }

  /**
   * Get the name of this file.
   */
  <<__Memoize>>
  final public function getPath(): Filesystem\Path {
    return new Filesystem\Path($this->filename);
  }

  /**
   * Get the size of the file.
   */
  final public function getSize(): int {
    return \filesize($this->filename);
  }
}
