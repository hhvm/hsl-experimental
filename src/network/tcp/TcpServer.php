<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\{Str, _Private};
use type Throwable;

/**
 * TCP socket server.
 */
final class TcpServer implements Server {
  /**
   * Servers are created using listen().
   */
  private function __construct(private resource $socket) {}

  /**
   * Create a TCP server listening on the given interface and port.
   */
  public static async function listen(
    IPAddress $ip,
    Port $port,
  ): Awaitable<TcpServer> {
    $err = 0;
    $errstr = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $socket = @\stream_socket_server(
      Str\format('tcp://%s:%d', $ip, $port),
      &$err,
      &$errstr,
    );
    if ($socket === false) {
      throw new SocketException($errstr, $err);
    }
    return new TcpServer($socket);
  }

  /**
   * {@inheritdoc}
   */
  public async function closeAsync(): Awaitable<void> {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    @fclose($this->socket);
  }


  /**
   * Get the local address of the socket.
   */
  public function getAddress(): Host {
    $ret = @\stream_socket_get_name($this->socket, false);
    if (false === $ret) {
      throw new SocketException('Unable to retrieve local address of the socket.');
    }

    return _Private\parseSocketAddress($ret);
  }

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?Port {
    $ret = @\stream_socket_get_name($this->socket, false);
    if (false === $ret) {
      throw new SocketException('Unable to retrieve local port of the socket.');
    }

    return _Private\parseSocketPort($ret);
  }

  /**
   * {@inheritdoc}
   */
  public async function accept(): Awaitable<SocketHandle> {
    $connection = @\stream_socket_accept($this->socket);
    if ($connection === false) {
      throw new SocketException('Unable to accept a socket.');
    }
    return _Private\NativeSocketHandle::create($connection);
  }
}
