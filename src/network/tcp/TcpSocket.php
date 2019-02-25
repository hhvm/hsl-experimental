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

use namespace HH\Lib\{Experimental\IO, C, Str, _Private};
use type Throwable;

/**
 * TCP socket connection.
 *
 * Sockets are created using connect() or TcpServer::accept().
 */
final class TcpSocket
  extends _Private\NativeSocketHandle
  implements SocketHandle {
  protected function __construct(private resource $impl) {
    parent::__construct($impl);
  }

  /**
   * Connect to the given peer (will automatically perform a DNS lookup for host names).
   */
  public static async function connect(
    Host $host,
    Port $port,
  ): Awaitable<TcpSocket> {
    $err = 0;
    $errstr = '';
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $socket = \stream_socket_client(
      Str\format('tcp://%s:%d', $host, $port),
      &$err,
      &$errstr,
      30.0,
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4106] __PHPStdLib */
      \STREAM_CLIENT_ASYNC_CONNECT,
    );
    if ($socket === false) {
      throw new SocketException($errstr, $err);
    }
    return new TcpSocket($socket);
  }

  /**
   * {@inheritdoc}
   */
  public function getReadHandle(): IO\ReadHandle {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWriteHandle(): IO\WriteHandle {
    return $this;
  }

  /**
   * Sets the TCP keep-alive timeout in seconds, 0 to disable keep-alive.
   */
  public function setKeepAlive(int $seconds): void {
    throw new SocketException('keep-alive option is not supported.');
  }

  /**
   * Disables Nagle's Algorithm when set.
   */
  public function setNoDely(bool $nodely): void {
    throw new SocketException('no-dely option is not supported.');
  }

  /**
   * Get the local address of the socket.
   */
  public function getAddress(): Host {
    $ret = @\stream_socket_get_name($this->impl, false);
    if (false === $ret) {
      throw
        new SocketException('Unable to retrieve local address of the socket.');
    }

    return _Private\parseSocketAddress($ret);
  }

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?Port {
    $ret = @\stream_socket_get_name($this->impl, false);
    if (false === $ret) {
      throw new SocketException('Unable to retrieve local port of the socket.');
    }

    return _Private\parseSocketPort($ret);
  }

  /**
   * Get the address of the remote peer.
   */
  public function getRemoteAddress(): Host {
    $ret = @\stream_socket_get_name($this->impl, true);
    if (false === $ret) {
      throw
        new SocketException('Unable to retrieve remote address of the socket.');
    }

    return _Private\parseSocketAddress($ret);
  }

  /**
   * Get the network port used by the remote peer (or NULL if not network port is being used).
   */
  public function getRemotePort(): ?Port {
    $ret = @\stream_socket_get_name($this->impl, true);
    if (false === $ret) {
      throw
        new SocketException('Unable to retrieve remote port of the socket.');
    }

    return _Private\parseSocketPort($ret);
  }
}
