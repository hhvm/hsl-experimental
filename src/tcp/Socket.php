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

use namespace HH\Lib\Experimental\{IO, Network};

<<__Sealed(DisposableSocket::class, NonDisposableSocket::class)>>
interface Socket extends Network\Socket {
  /** Returns the local address and port */
  public function getLocalAddress(): (string, int);
  /** Returns the remote address and port */
  public function getPeerAddress(): (string, int);
}
