<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Str;

enum Encoding: string {
  ASCII = 'ASCII';
  BASE64 = 'BASE64';
  EUC_CN = 'EUC-CN';
  EUC_JP = 'EUC-JP';
  EUC_KR = 'EUC-KR';
  EUC_TW = 'EUC-TW';
  HTML_ENTITIES = 'HTML-ENTITIES';
  ISO_8859_1 = 'ISO-8859-1';
  ISO_8859_2 = 'ISO-8859-2';
  ISO_8859_3 = 'ISO-8859-3';
  ISO_8859_4 = 'ISO-8859-4';
  ISO_8859_5 = 'ISO-8859-5';
  ISO_8859_6 = 'ISO-8859-6';
  ISO_8859_7 = 'ISO-8859-7';
  ISO_8859_8 = 'ISO-8859-8';
  ISO_8859_9 = 'ISO-8859-9';
  ISO_8859_10 = 'ISO-8859-10';
  ISO_8859_13 = 'ISO-8859-13';
  ISO_8859_14 = 'ISO-8859-14';
  ISO_8859_15 = 'ISO-8859-15';
  ISO_8859_16 = 'ISO-8859-16';
  ISO_2022_KR = 'ISO-2022-KR';
  JIS = 'JIS';
  KOI8_R = 'KOI8-R';
  KOI8_U = 'KOI8-U';
  QUOTED_PRINTABLE = 'Quoted-Printable';
  SJIS = 'SJIS';
  UCS2 = 'UCS-2';
  UCS2BE = 'UCS-2BE';
  UCS2LE = 'UCS-2LE';
  UCS4 = 'UCS-4';
  UCS4BE = 'UCS-4BE';
  UCS4LE = 'UCS-4LE';
  UTF32 = 'UTF-32';
  UTF32BE = 'UTF-32BE';
  UTF32LE = 'UTF-32LE';
  UTF16 = 'UTF-16';
  UTF16BE = 'UTF-16BE';
  UTF16LE = 'UTF-16LE';
  UTF8 = 'UTF-8';
  UTF7 = 'UTF-7';
  UUENCODE = 'UUENCODE';
  WINDOWS_1251 = 'Windows-1251';
  WINDOWS_1252 = 'Windows-1252';
  WINDOWS_1254 = 'Windows-1254';
}
