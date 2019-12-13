<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Unix;

use namespace HH\Lib\Experimental\Network;

final class Server
  implements Network\Server<Socket, DisposableSocket, CloseableSocket> {
  const type TAddress = string;

  private function __construct(private resource $impl) {
  }

  public static async function createAsync(string $path): Awaitable<this> {
    return await Network\_Private\socket_create_bind_listen_async(
      \AF_UNIX,
      \SOCK_STREAM,
      /* proto = */ 0,
      $path,
      /* port = */ 0,
    )
      |> new self($$);
  }

  <<__ReturnDisposable>>
  public async function nextConnectionAsync(): Awaitable<DisposableSocket> {
    return new _Private\DisposableSocket(await $this->nextConnectionNDAsync());
  }

  public async function nextConnectionNDAsync(): Awaitable<CloseableSocket> {
    return await Network\_Private\socket_accept_async($this->impl)
      |> new _Private\CloseableSocket($$);
  }

  public function getLocalAddress(): string {
    return Network\_Private\get_sock_name($this->impl)[0];
  }
}
