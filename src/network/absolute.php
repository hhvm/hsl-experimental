<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Network;

use namespace HH\Lib\{Experimental\IO, C, Str};

type Port = int;
type Host = string;
newtype IPAddress as Host = Host;
newtype IPv4Address as IPAddress = IPAddress;
newtype IPv6Address as IPAddress = IPAddress;

function ip(string $ip): IPAddress {
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  invariant(\filter_var($ip, \FILTER_VALIDATE_IP), 'Invalid IP Address');
  return $ip;
}

function host(string $host): Host {
  $ip = \gethostbyname($host);
  \invariant($ip !== $host && $ip !== false, 'Invalid hostname');
  return $host;
}

function ipv4(string $ip): IPv4Address {
  $address = ip($ip);
  \invariant(is_ipv4($address), 'Address is not a valid IPV4.');
  return $address;
}

function ipv6(string $ip): IPv6Address {
  $address = ip($ip);
  \invariant(is_ipv6($address), 'Address is not a valid IPV6.');
  return $address;
}

function is_ipv4(IPAddress $address): bool {
  return $address === \filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4);
}

function is_ipv6(IPAddress $address): bool {
  return $address === \filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6);
}
