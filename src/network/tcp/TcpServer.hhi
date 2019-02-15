<?hh // strict

namespace HH\Lib\Experimental\Network;

use type Throwable;

/**
 * TCP socket server.
 */
final class TcpServer implements Server {
  /**
   * Enable / disable simultaneous asynchronous accept requests that are queued by the operating system
   * when listening for new TCP connections.
   */
  const int SIMULTANEOUS_ACCEPTS = 150;

  /**
   * Servers are created using listen().
   */
  private function __construct();

  /**
   * Create a TCP server listening on the given interface and port.
   */
  public static function listen(
    string $host,
    int $port,
    ?TlsServerEncryption $tls = null,
  ): TcpServer;

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
  public function setOption(int $option, $value): bool;

  /**
   * {@inheritdoc}
   */
  public function accept(): SocketHandle;
}
