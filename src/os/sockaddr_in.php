<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\OS;

/** Address of an INET (IPv4) socket.
 *
 * See `man 7 ip` (Linux) or `man 4 inet` (BSD) for details.
 *
 * @see `sockaddr_in6` for INET6 (IPv6) sockets.
 */
final class sockaddr_in extends sockaddr {
  /** Construct a `sockaddr_in`.
   *
   * @param $port is the port to connect to, in network byte order - see
   *   `htons()`.
   * @param $address is the IP address to connect to, as a 32-bit integer, in
   *   network byte order - see `htonl()`.
   */
  public function __construct(
    private NetShort $port,
    private NetLong $address,
  ) {
  }

  <<__Override>>
  final public function getFamily(): AddressFamily {
    return AddressFamily::AF_INET;
  }

  /** Get the port, in network byte order.
   *
   * @see `ntohs()`
  **/
  final public function getPort(): NetShort {
    return $this->port;
  }

  /** Get the IP address, as a 32-bit integer, in network byte order.
   *
   * `in_addr` is an alias for `NetLong`.
   *
   * @see `ntohl()`
   */
  final public function getAddress(): in_addr {
    return $this->address;
  }

  final public function __debugInfo(): darray<string, mixed> {
    return darray[
      'port (network byte order)' => $this->port,
      'port (host byte order)' => ntohs($this->port),
      'address (network byte order uint32)' => $this->address,
      'address (presentation format)' =>
        inet_ntop(AddressFamily::AF_INET, $this->address),
    ];
  }
}
