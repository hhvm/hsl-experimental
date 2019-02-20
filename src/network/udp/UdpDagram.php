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

/**
 * Wrapper for a UDP datagram.
 */
final class UdpDatagram {
  /**
   * Create a new UDP datagram.
   * 
   * @param string $data Payload to be transmitted.
   * @param IPAddress $address IP address of the remote peer.
   * @param Port $port Port being used by the remote peer.
   */
  public function __construct(
    private string $data,
    private IPAddress $address,
    private Port $port,
  ) {}

  /**
   * Get the Playload to be transmitted.
   */
  public function getData(): string {
    return $this->data;
  }

  /**
   * Get the remote peer ip address and port.
   */
  public function getPeer(): (IPAddress, Port) {
    return tuple($this->address, $this->port);
  }

  /**
   * Create a UDP datagram with the same remote peer.
   * 
   * @param string $data Data to be transmitted.
   */
  public function withData(string $data): UdpDatagram {
    return new self($data, $this->address, $this->port);
  }

  /**
   * Create a datagram with the same transmitted data.
   * 
   * @param IPAddress $address IP address of the remote peer.
   * @param Port $port Port being used by the remote peer.
   */
  public function withPeer(IPAddress $address, Port $port): UdpDatagram {
    return new self($this->data, $address, $port);
  }
}
