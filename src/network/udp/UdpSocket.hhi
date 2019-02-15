<?hh // strict

namespace HH\Lib\Experimental\Network;

use type Throwable;

/**
 * UDP socket API.
 */
final class UdpSocket implements Socket {
  /**
   * Sets the maximum number of packet forwarding operations performed by routers.
   */
  const int TTL = 200;

  /**
   * Set to true to have multicast packets loop back to local sockets.
   */
  const int MULTICAST_LOOP = 250;

  /**
   * Sets the maximum number of packet forwarding operations performed by routers for multicast packets.
   */
  const int MULTICAST_TTL = 251;

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
  public function close(?Throwable $e = null): void;

  /**
   * {@inheritdoc}
   */
  public function getAddress(): string;

  /**
   * {@inheritdoc}
   */
  public function getPort(): ?int;

  /**
   * {@inheritdoc}
   */
  public function setOption(int $option, mixed $value): bool;

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
  public function sendAsync(UdpDatagram $datagram): int;
}
