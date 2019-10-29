<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\OS\_Private;

// hackfmt-ignore
enum GNU_GAIError : int {
  EAI_AGAIN        = -3;
  EAI_BADFLAGS     = -1;
  EAI_FAIL         = -4;
  EAI_FAMILY       = -6;
  EAI_MEMORY       = -10;
  EAI_NONAME       = -2;
  EAI_OVERFLOW     = -12;
  EAI_SERVICE      = -8;
  EAI_SOCKTYPE     = -7;
  EAI_SYSTEM       = -11;

  // GNU Extensions
  EAI_NODATA       = -5;
  EAI_ADDRFAMILY   = -9;
  EAI_INPROGRESS   = -100;
  EAI_CANCELLED    = -101;
  EAI_NOTCANCELLED = -102;
  EAI_ALLDONE      = -103;
  EAI_INTR         = -104;
  EAI_IDN_ENCODE   = -105;
}

// hackfmt-ignore
enum MacOS_GAIError : int {
  EAI_AGAIN        = 2;
  EAI_BADFLAGS     = 3;
  EAI_FAIL         = 4;
  EAI_FAMILY       = 5;
  EAI_MEMORY       = 6;
  EAI_NONAME       = 8;
  EAI_OVERFLOW     = 14;
  EAI_SERVICE      = 9;
  EAI_SOCKTYPE     = 10;
  EAI_SYSTEM       = 11;

  // MacOS Extensions
  EAI_ADDRFAMILY   = 1;
  EAI_BADHINTS     = 12;
  EAI_NODATA       = 7;
  EAI_PROTOCOL     = 13;
}

/** OS-level error constants from netdb.h, used by gai_strerror() */
enum GAIError : string {
  EAI_ADDRFAMILY = "EAI_ADDRFAMILY";
  EAI_AGAIN = "EAI_AGAIN";
  EAI_ALLDONE = "EAI_ALLDONE";
  EAI_BADFLAGS = "EAI_BADFLAGS";
  EAI_BADHINTS = "EAI_BADHINTS";
  EAI_CANCELLED = "EAI_CANCELLED";
  EAI_FAIL = "EAI_FAIL";
  EAI_FAMILY = "EAI_FAMILY";
  EAI_IDN_ENCODE = "EAI_IDN_ENCODE";
  EAI_INPROGRESS = "EAI_INPROGRESS";
  EAI_INTR = "EAI_INTR";
  EAI_MEMORY = "EAI_MEMORY";
  EAI_NODATA = "EAI_NODATA";
  EAI_NONAME = "EAI_NONAME";
  EAI_NOTCANCELLED = "EAI_NOTCANCELLED";
  EAI_OVERFLOW = "EAI_OVERFLOW";
  EAI_PROTOCOL = "EAI_PROTOCOL";
  EAI_SERVICE = "EAI_SERVICE";
  EAI_SOCKTYPE = "EAI_SOCKTYPE";
  EAI_SYSTEM = "EAI_SYSTEM";
}

function gaierror_from_int(int $value): GAIError {
  if (IS_MACOS) {
    $names = MacOS_GAIError::getNames();
  } else {
    $names = GNU_GAIError::getNames();
  }
  /* HH_FIXME[4324] bad type for $value */
  return GAIError::assert($names[$value] ?? ((string) $value));
}
