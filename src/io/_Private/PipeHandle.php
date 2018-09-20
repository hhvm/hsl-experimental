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

use namespace HH\Lib\Experimental\IO;

final class PipeHandle extends NativeHandle {
  public static function createPair(): (IO\ReadHandle, IO\WriteHandle) {
    /* HH_IGNORE_ERROR[2049] intentionally not in HHI */
    /* HH_IGNORE_ERROR[4107] intentionally not in HHI */
    list($r, $w) = Native\pipe() as (resource, resource);
    return tuple(new self($r), new self($w));
  }
}
