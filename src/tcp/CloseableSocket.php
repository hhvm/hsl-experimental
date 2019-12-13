<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\TCP;

use namespace HH\Lib\Experimental\Network;

<<__Sealed(_Private\CloseableTCPSocket::class)>>
interface CloseableSocket
  extends Socket, Network\CloseableSocket {
}
