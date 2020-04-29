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

/** Address of an INET6 (IPv6) socket.
 *
 * See `man 7 ip6` (Linux) or `man 4 inet6` (BSD) for details.
 *
 * @see `sockaddr_in` for INET (IPv4) sockets.
 */
final class sockaddr_in6 extends sockaddr {
  /** Construct an instance.
   *
   * @param $port is the port to connect to, in network byte order - see
   *   `htons()`.
   * @param $address is the IP address to connect to, as a 32-bit integer, in
   *   network byte order - see `htonl()`.
   */
  public function __construct(
    private NetShort $port,
    private NetLong $flowInfo,
    private in6_addr $address,
    private NetLong $scopeID,
  ) {
  }

  <<__Override>>
  final public function getFamily(): AddressFamily {
    return AddressFamily::AF_INET6;
  }

  /** Get the port, in network byte order.
   *
   * @see `ntohs()`
  **/
  final public function getPort(): NetShort {
    return $this->port;
  }

  final public function getAddress(): in6_addr {
    return $this->address;
  }

  final public function getFlowInfo(): NetLong {
    return $this->flowInfo;
  }

  final public function getScopeID(): NetLong {
    return $this->scopeID;
  }

  final public function __debugInfo(): darray<string, mixed> {
    return darray[
      'port (network byte order)' => $this->port,
      'port (host byte order)' => ntohs($this->port),
      'flow info (network byte order)' => $this->flowInfo,
      'flow info (host byte order)' => ntohl($this->flowInfo),
      'scope ID (network byte order)' => $this->scopeID,
      'scope ID (host byte order)' => ntohl($this->scopeID),
      'address (network format)' => $this->address,
      'address (presentation format)' =>
        inet_ntop(AddressFamily::AF_INET6, $this->address),
    ];
  }
}
