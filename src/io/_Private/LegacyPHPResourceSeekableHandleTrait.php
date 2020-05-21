<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_IO;

use namespace HH\Lib\{IO, Str};
use type HH\Lib\_Private\PHPWarningSuppressor;

// @lint-ignore-every PHP_IGNORE_ERROR

trait LegacyPHPResourceSeekableHandleTrait implements IO\SeekableHandle {
  require extends LegacyPHPResourceHandle;
  /**
   * Move to a specific offset within a handle.
   *
   * Offset is relative to the start of the handle - so, the beginning of the
   * handle is always offset 0.
   */
  final public function seek(int $offset): void {
    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \fseek($this->impl, $offset);
  }

  final public function tell(): int {
    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return \ftell($this->impl);
  }
}
