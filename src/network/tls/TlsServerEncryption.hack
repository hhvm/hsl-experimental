namespace HH\Lib\Experimental\Network;

/**
 * Socket server encryption settings.
 */
final class TlsServerEncryption {

  public function __construct(
    private TlsCertificate $defaultCertificate,
    private (string, TlsCertificate) $certificate,
    private Container<string> $alpnProtocols,
    private string $certificateAuthorityPath,
    private string $certificateAuthorityFile,
  ) {}

  public function getDefaultCertificate(): TlsCertificate {
    return $this->defaultCertificate;
  }

  public function getCertificate(): TlsCertificate {
    return $this->certificate[1];
  }

  public function getCertificateHost(): string {
    return $this->certificate[0];
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
   * Configure the default X509 certificate to be used by the server.
   */
  public function withDefaultCertificate(
    TlsCertificate $certificate,
  ): TlsServerEncryption {
    return new TlsServerEncryption(
      $certificate,
      $this->certificate,
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Configure a host-based X509 certificate to be used by the server.
   * 
   * @param string $host Hostname.
   */
  public function withCertificate(
    string $host,
    TlsCertificate $certificate,
  ): TlsServerEncryption {
    return new TlsServerEncryption(
      $this->defaultCertificate,
      tuple($host, $certificate),
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $this->certificateAuthorityFile,
    );
  }

  /**
   * Set list of available ALPN protocol names.
   */
  public function withAlpnProtocols(string ...$protocols): TlsServerEncryption {
    return new TlsServerEncryption(
      $this->defaultCertificate,
      $this->certificate,
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
  ): TlsServerEncryption {
    return new TlsServerEncryption(
      $this->defaultCertificate,
      $this->certificate,
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
  ): TlsServerEncryption {
    return new TlsServerEncryption(
      $this->defaultCertificate,
      $this->certificate,
      $this->alpnProtocols,
      $this->certificateAuthorityPath,
      $file,
    );
  }
}
