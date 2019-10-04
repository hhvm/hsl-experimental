<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\TCP;

use namespace HH\Lib\_Private;
use namespace HH\Lib\Experimental\IO;

<<__Sealed(_Private\DisposableTCPSocket::class)>>
interface DisposableSocket
  extends \IAsyncDisposable, IO\DisposableReadWriteHandle, Socket {
}
