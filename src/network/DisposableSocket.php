<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\Experimental\{IO, TCP, UnixSocket};

<<__Sealed(TCP\DisposableSocket::class, UnixSocket\DisposableSocket::class)>>
interface DisposableSocket extends Socket, IO\DisposableReadWriteHandle {
}
