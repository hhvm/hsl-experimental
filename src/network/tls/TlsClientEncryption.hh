<?hh // strict

namespace HH\Lib\Experimental\Network;

/**
 * Immutable Socket client encryption settings.
 */
final class TlsClientEncryption {
  public function __construct(
    private bool $allowSelfSigned,
    private int $depth,
    private string $peerName,
    private Container<string> $alpnProtocols,
    private string $certificateAuthorityPath,
    private string $certificateAuthorityFile,
  ) {}

  public function allowSelfSigned(): bool {
    return $this->allowSelfSigned;
  }

  public function getVerifyDepth(): int {
    return $this->depth;
  }

  public function getPeerName(): string {
    return $this->peerName;
  }

  public function getAlpnProtocols(): Container<string> {
    return $this->alpnProtocols;
  }

  public function getCertificateAuthorityPath(): string {
    return $this->certificateAuthorityPath;
  }

  public function getCertificateAuthorityFile(): string {
    return $this->certificateAuthorityFile;
  }

  /**
   * Allow connecting to hosts that have a self-signed X509 certificate.
   */
  public function withAllowSelfSigned(bool $allow): TlsClientEncryption {
    return new TlsClientEncryption(
      $allow,
      $this->depth,
      $this->peerName,
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Restrict the maximum certificate validation chain to the given length.
   */
  public function withVerifyDepth(int $depth): TlsClientEncryption {
    return new TlsClientEncryption(
      $this->allowSelfSigned,
      $depth,
      $this->peerName,
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Set peer name to connect to.
   */
  public function withPeerName(string $name): TlsClientEncryption {
    return new TlsClientEncryption(
      $this->allowSelfSigned,
      $this->depth,
      $name,
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Set list of acceptable ALPN protocol names.
   */
  public function withAlpnProtocols(string ...$protocols): TlsClientEncryption {
    return new TlsClientEncryption(
      $this->allowSelfSigned,
      $this->depth,
      $this->peerName,
      $protocols,
      $this->certificateAuthorityPath,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Provide the path of a CA dir to be used.
   */
  public function withCertificateAuthorityPath(
    string $path,
  ): TlsClientEncryption {
    return new TlsClientEncryption(
      $this->allowSelfSigned,
      $this->depth,
      $this->peerName,
      $this->alpnProtocols,
      $path,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Provide the CA file to be used.
   */
  public function withCertificateAuthorityFile(
    string $file,
  ): TlsClientEncryption {
    return new TlsClientEncryption(
      $this->allowSelfSigned,
      $this->depth,
      $this->peerName,
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $file,
    );
  }
}
