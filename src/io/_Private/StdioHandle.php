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

final class StdioHandle extends NativeHandle {
  <<__Memoize>>
  public static function stderr(): IO\WriteHandle {
    return new self(\STDERR);
  }

  <<__Memoize>>
  public static function stdout(): IO\WriteHandle {
    return new self(\STDOUT);
  }

  <<__Memoize>>
  public static function stdin(): IO\ReadHandle {
    return new self(\STDIN);
  }
}
