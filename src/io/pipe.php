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

/** Create a pair of handles, where writes to the `WriteHandle` can be
 * read from the `ReadHandle`.
 */
function pipe(): (ReadHandle, WriteHandle) {
  return _Private\PipeHandle::createPair();
}
