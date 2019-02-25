<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\{IO, Network};
use namespace HH\Lib\Str;

<<__Sealed(Network\TcpSocket::class)>>
class NativeSocketHandle extends NativeHandle implements Network\SocketHandle {
  protected function __construct(private resource $socket) {
    parent::__construct($socket);
  }

  public static function create(resource $socket): NativeSocketHandle {
    return new NativeSocketHandle($socket);
  }

  /**
   * Get the local address of the socket.
   */
  public function getAddress(): Network\Host {
    $ret = @\stream_socket_get_name($this->socket, false);
    if (false === $ret) {
      throw new Network\SocketException(
        'Unable to retrieve local address of the socket.',
      );
    }

    return parseSocketAddress($ret);
  }

  /**
   * Get the local network port, or NULL when no port is being used.
   */
  public function getPort(): ?Network\Port {
    $ret = @\stream_socket_get_name($this->socket, false);
    if (false === $ret) {
      throw new Network\SocketException(
        'Unable to retrieve local port of the socket.',
      );
    }

    return parseSocketPort($ret);
  }

  /**
   * Get the address of the remote peer.
   */
  public function getRemoteAddress(): Network\Host {
    $ret = @\stream_socket_get_name($this->socket, true);
    if (false === $ret) {
      throw new Network\SocketException(
        'Unable to retrieve remote address of the socket.',
      );
    }

    return parseSocketAddress($ret);
  }

  /**
   * Get the network port used by the remote peer (or NULL if not network port is being used).
   */
  public function getRemotePort(): ?Network\Port {
    $ret = @\stream_socket_get_name($this->socket, true);
    if (false === $ret) {
      throw new Network\SocketException(
        'Unable to retrieve remote port of the socket.',
      );
    }

    return parseSocketPort($ret);
  }
}
