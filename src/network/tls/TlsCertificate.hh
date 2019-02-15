<?hh // strict

namespace HH\Lib\Experimental\Network;

newtype TlsCertificate = shape(
  /**
   * Path to the certificate file.
   */
  'cert' => string,

  /**
   * Path to the secret key file.
   */
  'key' => string,

  /**
   * Passphrase being used to access the secret key.
   */
  'passphrase' => ?string,
  ...
);
