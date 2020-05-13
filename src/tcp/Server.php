<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\TCP;

use namespace HH\Lib\Network;
use namespace HH\Lib\_Private\{_Network, _TCP};

final class Server implements Network\Server<CloseableSocket> {
  /** Host and port */
  const type TAddress = (string, int);

  private function __construct(private resource $impl) {
  }

  /** Create a bound and listening instance */
  public static async function createAsync(
    Network\IPProtocolVersion $ipv,
    string $host,
    int $port,
    ServerOptions $opts = shape(),
  ): Awaitable<this> {
    switch ($ipv) {
      case Network\IPProtocolVersion::IPV6:
        $af = \AF_INET6;
        break;
      case Network\IPProtocolVersion::IPV4:
        $af = \AF_INET;
        break;
    }

    return await _Network\socket_create_bind_listen_async(
      $af,
      \SOCK_STREAM,
      \SOL_TCP,
      $host,
      $port,
      $opts['socket_options'] ?? shape(),
    )
      |> new self($$);
  }

  public async function nextConnectionAsync(): Awaitable<CloseableSocket> {
    return await _Network\socket_accept_async($this->impl)
      |> new _TCP\CloseableTCPSocket($$);
  }

  public function getLocalAddress(): (string, int) {
    return _Network\get_sock_name($this->impl);
  }

  public function stopListening(): void {
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    \socket_close($this->impl);
  }
}
