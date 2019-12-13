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

/** An `IO\Handle` that is readable. */
interface ReadHandle extends Handle {
  /** An immediate, unordered blocking read.
   *
   * You almost certainly don't want to call this; instead, use
   * `readAsync()` or `readLineAsync()`, which are wrappers around
   * this
   */
  public function rawReadBlocking(?int $max_bytes = null): string;

  /** Read until we reach `$max_bytes`, or the end of the file. */
  public function readAsync(
    ?int $max_bytes = null,
    ?float $timeout_seconds = null,
  ): Awaitable<string>;

  /** Read until we reach `$max_bytes`, the end of the file, or the
   * end of the line */
  public function readLineAsync(
    ?int $max_bytes = null,
    ?float $timeout_seconds = null,
  ): Awaitable<string>;
}
