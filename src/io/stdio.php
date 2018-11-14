<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

use namespace HH\Lib\_Private;

function stdout(): WriteHandle {
  return _Private\StdioHandle::stdout();
}

function stderr(): WriteHandle {
  return _Private\StdioHandle::stderr();
}

function stdin(): ReadHandle {
  return _Private\StdioHandle::stdin();
}
