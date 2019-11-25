<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File;

use namespace HH\Lib\Experimental\IO;

interface NonDisposableHandle extends IO\NonDisposableSeekableHandle, Handle {
}

interface NonDisposableReadHandle
  extends IO\NonDisposableSeekableReadHandle, NonDisposableHandle, ReadHandle {
}

interface NonDisposableWriteHandle
  extends
    IO\NonDisposableSeekableWriteHandle,
    NonDisposableHandle,
    WriteHandle {
}

interface NonDisposableReadWriteHandle
  extends
    IO\NonDisposableSeekableReadWriteHandle,
    ReadWriteHandle,
    NonDisposableReadHandle,
    NonDisposableWriteHandle {
}
