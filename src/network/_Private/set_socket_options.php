<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network\_Private;

use type HH\Lib\Experimental\Network\SocketOptions;

function set_socket_options(resource $sock, SocketOptions $opts): void {
  if ($opts['SO_REUSEADDR'] ?? false) {
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    \socket_set_option($sock, \SOL_SOCKET, \SO_REUSEADDR, 1);
  }
}
