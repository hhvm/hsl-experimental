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

use type Throwable;

/**
 * UDP socket API.
 */
final class UdpSocket implements Socket {
  /**
   * Bind a UDP socket to the given local peer.
   * 
   * @param IPAddress $address Local network interface address (IP) to be used.
   * @param Port $port Local port to be used.
   */
  public static function bind(IPAddress $address, Port $port): UdpSocket;

  /**
   * Bind a UDP socket and join the given UDP multicast group.
   * 
   * @param IPAddress $group Address (IP) of the UDP multicast group.
   * @param Port $port Port being used by the UDP multicast group.
   */
  public static function multicast(IPAddress $group, Port $port): UdpSocket;

  /**
   * {@inheritdoc}
   */
  public function closeAsync(): Awaitable<void>;

  /**
   * {@inheritdoc}
   */
  public function getAddress(): Host;

  /**
   * {@inheritdoc}
   */
  public function getPort(): ?Port;

  /**
   * Sets the maximum number of packet forwarding operations performed by routers.
   */
  public function setTtl(int $max_packet): void;

  /**
   * Set to true to have multicast packets loop back to local sockets.
   */
  public function setMulticastLoop(bool $multicast_loop): void;

  /**
   * Sets the maximum number of packet forwarding operations performed by routers for multicast packets.
   */
  public function setMulticastTtl(int $max_packet): void;

  /**
   * Receive the next UDP datagram from the socket.
   */
  public function receive(): UdpDatagram;

  /**
   * Transmit the given UDP datagram over the network.
   * 
   * @param UdpDatagram $datagram UDP datagram with payload and remote peer address.
   */
  public function send(UdpDatagram $datagram): void;

  /**
   * Enque the given UDP datagram to be sent over the network.
   * 
   * The datagram will only be enqueued if it cannot be sent immediately.
   * 
   * @param UdpDatagram $datagram UDP datagram with payload and remote peer address.
   */
  public function sendAsync(UdpDatagram $datagram): Awaitable<void>;

  /**
   * Get socket type.
   */
  public function getType(): SocketType;

  /**
   * {@inheritdoc}
   */
  public function isEndOfFile(): bool;
}
