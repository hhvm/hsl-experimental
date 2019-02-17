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

/**
 * type Host = string;
 * type Port = int;
 * type IP = string;
 * newtype IPV4 as IP = IP;
 * newtype IPV6 as IP = IP;
 * newtype Address<Tv> = Host;
 **/

function ip(string $ip): string {
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  invariant(\filter_var($ip, \FILTER_VALIDATE_IP), 'Invalid IP Address');
  return is_ipv4($ip) ? ipv4($ip) : ipv6($ip);
}

function host(string $host): string {
  $ip = \gethostbyname($host);
  \invariant($ip !== $host, 'Invalid hostname');
  return $host;
}

function ipv4(string $ip): string {
  $address = ip($ip);
  \invariant(is_ipv4($address), 'Address is not a valid IPV4.');
  return $address;
}

function ipv6(string $ip): string {
  $address = ip($ip);
  \invariant(is_ipv6($address), 'Address is not a valid IPV6.');
  return $address;
}

function is_ipv4(string $address): bool {
  return \filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4);
}

function is_ipv6(string $address): bool {
  return \filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6);
}
