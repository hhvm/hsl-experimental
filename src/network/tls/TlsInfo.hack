namespace HH\Lib\Experimental\Network;

newtype TlsInfo = shape(
  /**
   * TLS protocol version.
   */
  'protocol' => string,

  /**
   * Cipher being used to encrypt data.
   */
  'cipher_name' => string,

  /**
   * Cipher bits being used to encrypt data.
   */
  'cipher_bits' => int,

  /**
   * Negotiated ALPN protocol name.
   */
  'alpn_protocol' => string,
  ...
);
