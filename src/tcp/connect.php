<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\TCP;

use namespace HH\Lib\{Network, OS};
use namespace HH\Lib\_Private\{_Network, _OS, _TCP};

/** Connect to a socket asynchronously, returning a non-disposable handle.
 *
 * If using IPv6 with a fallback to IPv4 with a connection timeout, the timeout
 * will apply separately to the IPv4 and IPv6 connection attempts.
 */
async function connect_nd_async(
  string $host,
  int $port,
  ConnectOptions $opts = shape(),
): Awaitable<CloseableSocket> {
  $ipver = $opts['ip_version'] ?? Network\IPProtocolBehavior::PREFER_IPV6;
  $timeout_ns = $opts['timeout_ns'] ?? 0;
  switch ($ipver) {
    case Network\IPProtocolBehavior::PREFER_IPV6:
      $sds = vec[OS\SocketDomain::PF_INET6, OS\SocketDomain::PF_INET];
      break;
    case Network\IPProtocolBehavior::FORCE_IPV6:
      $sds = vec[OS\SocketDomain::PF_INET6];
      break;
    case Network\IPProtocolBehavior::FORCE_IPV4:
      $sds = vec[OS\SocketDomain::PF_INET];
      break;
  }

  $ex = null;
  foreach ($sds as $sd) {
    $sock = OS\socket($sd, OS\SocketType::SOCK_STREAM, 0);
    $sa = null;
    switch ($sd) {
      case OS\SocketDomain::PF_INET:
        $ipv4_host = _Network\resolve_hostname(
          OS\AddressFamily::AF_INET,
          $host,
        );
        if ($ipv4_host is nonnull) {
          $sa = new OS\sockaddr_in(
            OS\htons($port),
            OS\inet_pton_inet($ipv4_host),
          );
        }
        break;
      case OS\SocketDomain::PF_INET6:
        $ipv6_host = _Network\resolve_hostname(
          OS\AddressFamily::AF_INET6,
          $host,
        );

        if ($ipv6_host !== null) {
          $sa = new OS\sockaddr_in6(
            OS\htons($port),
            /* flowinfo = */ OS\htonl(0),
            OS\inet_pton_inet6($ipv6_host),
            /* scope id = */ OS\htonl(0),
          );
        }
        break;
      case OS\SocketDomain::PF_UNIX:
        invariant_violation('unreachable');
    }

    if ($sa === null) {
      continue;
    }

    try {
      await _Network\socket_connect_async($sock, $sa, $timeout_ns);
      return new _TCP\CloseableTCPSocket($sock);
    } catch (OS\ErrnoException $this_sd_ex) {
      $ex = $this_sd_ex;
    }
  }
  if ($ex === null) {
    _OS\throw_errno(
      OS\Errno::EINVAL,
      "Failed to create a sockaddr for any domain",
    );
  }
  throw $ex;
}

/** Connect to a socket asynchronously, returning a disposable handle.
 *
 * If using IPv6 with a fallback to IPv4 with a connection timeout, the timeout
 * will apply separately to the IPv4 and IPv6 connection attempts.
 */
<<__ReturnDisposable>>
async function connect_async(
  string $host,
  int $port,
  ConnectOptions $opts = shape(),
): Awaitable<DisposableSocket> {
  $nd = await connect_nd_async($host, $port, $opts);
  return new _TCP\DisposableTCPSocket($nd);
}
