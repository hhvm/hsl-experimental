<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Unix;

use namespace HH\Lib\Network;
use namespace HH\Lib\_Private\{_Network, _Unix};

final class Server implements Network\Server<CloseableSocket> {
  /** Path */
  const type TAddress = string;

  private function __construct(private resource $impl) {
  }

  /** Create a bound and listening instance */
  public static async function createAsync(string $path): Awaitable<this> {
    return await _Network\socket_create_bind_listen_async(
      \AF_UNIX,
      \SOCK_STREAM,
      /* proto = */ 0,
      $path,
      /* port = */ 0,
    )
      |> new self($$);
  }

  public async function nextConnectionAsync(): Awaitable<CloseableSocket> {
    return await _Network\socket_accept_async($this->impl)
      |> new _Unix\CloseableSocket($$);
  }

  public function getLocalAddress(): string {
    return _Network\get_sock_name($this->impl)[0];
  }

  public function stopListening(): void {
    /* HH_FIXME[2049] PHPStdLib */
    /* HH_FIXME[4107] PHPStdLib */
    \socket_close($this->impl);
  }
}
