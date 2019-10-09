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

use namespace HH\Lib\Experimental\Fileystem;
use namespace HH\Lib\_Private;

interface ReadHandle extends Handle {
  public function rawReadBlocking(?int $max_bytes = null): string;

  /** Read until we reach `$max_bytes`, or the end of the file. */
  public function readAsync(?int $max_bytes = null): Awaitable<string>;
  /** Read until we reach `$max_bytes`, or the end of the line. */
  public function readLineAsync(?int $max_bytes = null): Awaitable<string>;
}
