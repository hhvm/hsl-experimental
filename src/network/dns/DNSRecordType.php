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

/**
 * @link https://en.wikipedia.org/wiki/List_of_DNS_record_types
 */
enum DNSRecordType: int {
  A = 1;
  AAAA = 28;
  AFSDB = 18;
  APL = 42;
  CCA = 257;
  CDNSKEY = 60;
  CDS = 59;
  CERT = 37;
  CNAME = 5;
  DHCID = 49;
  DLV = 32769;
  DNAME = 39;
  DNSKEY = 48;
  DS = 43;
  HIP = 55;
  IPSECKEY = 45;
  KEY = 25;
  KX = 36;
  LOC = 29;
  MX = 15;
  NAPTR = 35;
  NS = 2;
  NSEC = 47;
  NSEC3 = 50;
  NSEC3PARAM = 51;
  OPENPGPKEY = 61;
  PTR = 12;
  RRSIG = 46;
  RP = 17;
  SIG = 24;
  SMIMEA = 53;
  SOA = 6;
  SRV = 33;
  SSHFP = 44;
  TA = 32768;
  TKEY = 249;
  TLSA = 52;
  TSIG = 250;
  TXT = 16;
  URI = 256;
}
