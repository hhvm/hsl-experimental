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

/**
 * Create a temporary file that gets removed as a disposable.
 */
final class TemporaryFile extends FileBase {
  public function __construct(
    private FileMode $mode,
  ) {
    parent::__construct(_Private\make_temporary_file(), $mode);
  }

  <<__Override>>
  protected function dispose(): void {
    remove_file($this->getName());
  }
}
