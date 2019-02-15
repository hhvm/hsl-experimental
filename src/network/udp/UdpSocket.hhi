<?hh // strict

namespace HH\Lib\Experimental\Network;

use type Throwable;

/**
 * UDP socket API.
 */
final class UdpSocket implements Socket {
  /**
   * Bind a UDP socket to the given local peer.
   * 
   * @param string $address Local network interface address (IP) to be used.
   * @param int $port Local port to be used.
   */
  public static function bind(string $address, int $port): UdpSocket;

  /**
   * Bind a UDP socket and join the given UDP multicast group.
   * 
   * @param string $group Address (IP) of the UDP multicast group.
   * @param int $port Port being used by the UDP multicast group.
   */
  public static function multicast(string $group, int $port): UdpSocket;

  /**
   * {@inheritdoc}
   */
  public function close(): void;

  /**
   * {@inheritdoc}
   */
  public function getAddress(): string;

  /**
   * {@inheritdoc}
   */
  public function getPort(): ?int;

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
   * @return int Number of bytes in the socket's send queue.
   */
  public function sendAsync(UdpDatagram $datagram): Awaitable<int>;
}
