<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_OS;

use namespace HH\Lib\{C, Str};
use namespace HH\Lib\OS;

// hackfmt-ignore
/** OS-level error number constants from `errno.h`.
 *
 * These values are typically stored in a global `errno` variable by C APIs.
 *
 * `0` is used to indicate success, but not defined in `errno.h`; we expect
 * Hack programs to use the `Errno` type when an error is known to have
 * occurred, or `?Errno` when an error /may/ have occurred.
 *
 * `EWOULDBLOCK` is not defined; use `EAGAIN` instead.
 * - on Linux, it is an alias for `EAGAIN`
 * - on MacOS, it is not defined
 *
 * `EDEADLOCK` is not defined; use `EDEADLK` instead.
 * - on Linux, it is an alias for `EDEADLK`
 * - on MacOS, it is not defined
 *
 * Negative values indicate that the constant is not defined on the current
 * operating system; for example, `ECHRNG` is not defined on MacOS.
 */
enum Errno: int as int {
  /* SUCCESS = 0 */
  EPERM           = 1;
  ENOENT          = 2;
  ESRCH           = 3;
  EINTR           = 4;
  EIO             = 5;
  ENXIO           = 6;
  E2BIG           = 7;
  ENOEXEC         = 8;
  EBADF           = 9;
  ECHILD          = 10;
  EAGAIN          = IS_MACOS ?   35 :   11;
  ENOMEM          = 12;
  EACCES          = 13;
  EFAULT          = 14;
  ENOTBLK         = 15;
  EBUSY           = 16;
  EEXIST          = 17;
  EXDEV           = 18;
  ENODEV          = 19;
  ENOTDIR         = 20;
  EISDIR          = 21;
  EINVAL          = 22;
  ENFILE          = 23;
  EMFILE          = 24;
  ENOTTY          = 25;
  ETXTBSY         = 26;
  EFBIG           = 27;
  ENOSPC          = 28;
  ESPIPE          = 29;
  EROFS           = 30;
  EMLINK          = 31;
  EPIPE           = 32;
  EDOM            = 33;
  ERANGE          = 34;
  EDEADLK         = IS_MACOS ?   11 :   35;
  ENAMETOOLONG    = IS_MACOS ?   63 :   36;
  ENOLCK          = IS_MACOS ?   77 :   37;
  ENOSYS          = IS_MACOS ?   78 :   38;
  ENOTEMPTY       = IS_MACOS ?   66 :   39;
  ELOOP           = IS_MACOS ?   62 :   40;
  /* EWOULDBLOCK = EAGAIN */
  ENOMSG          = IS_MACOS ?   91 :   42;
  EIDRM           = IS_MACOS ?   90 :   43;

  ECHRNG          = IS_MACOS ?  -44 :   44;
  EL2NSYNC        = IS_MACOS ?  -45 :   45;
  EL3HLT          = IS_MACOS ?  -46 :   46;
  EL3RST          = IS_MACOS ?  -47 :   47;
  ELNRNG          = IS_MACOS ?  -48 :   48;
  EUNATCH         = IS_MACOS ?  -49 :   49;
  ENOCSI          = IS_MACOS ?  -50 :   50;
  EL2HLT          = IS_MACOS ?  -51 :   51;
  EBADE           = IS_MACOS ?  -52 :   52;
  EBADR           = IS_MACOS ?  -53 :   53;
  EXFULL          = IS_MACOS ?  -54 :   54;
  ENOANO          = IS_MACOS ?  -55 :   55;
  EBADRQC         = IS_MACOS ?  -56 :   56;
  EBADSLT         = IS_MACOS ?  -57 :   57;
  /* EDEADLOCK = EDEADLK */

  EBFONT          = IS_MACOS ?  -59 :   59;
  ENOSTR          = IS_MACOS ?   99 :   60;
  ENODATA         = IS_MACOS ?   96 :   61;
  ETIME           = IS_MACOS ?  101 :   62;
  ENOSR           = IS_MACOS ?   98 :   63;
  ENONET          = IS_MACOS ?  -64 :   64;
  ENOPKG          = IS_MACOS ?  -65 :   65;
  EREMOTE         = IS_MACOS ?  -66 :   66;
  ENOLINK         = IS_MACOS ?   97 :   67;
  EADV            = IS_MACOS ?  -68 :   68;
  ESRMNT          = IS_MACOS ?  -69 :   69;
  ECOMM           = IS_MACOS ?  -70 :   70;
  EPROTO          = IS_MACOS ?  100 :   71;
  EMULTIHOP       = IS_MACOS ?   95 :   72;
  EDOTDOT         = IS_MACOS ?  -73 :   73;
  EBADMSG         = IS_MACOS ?   94 :   74;
  EOVERFLOW       = IS_MACOS ?   84 :   75;
  ENOTUNIQ        = IS_MACOS ?  -76 :   76;
  EBADFD          = IS_MACOS ?  -77 :   77;
  EREMCHG         = IS_MACOS ?  -78 :   78;

  ELIBACC         = IS_MACOS ?  -79 :   79;
  ELIBBAD         = IS_MACOS ?  -80 :   80;
  ELIBSCN         = IS_MACOS ?  -81 :   81;
  ELIBMAX         = IS_MACOS ?  -82 :   82;
  ELIBEXEC        = IS_MACOS ?  -83 :   83;

  EILSEQ          = IS_MACOS ?   92 :   84;
  ERESTART        = IS_MACOS ?  -85 :   85;
  ESTRPIPE        = IS_MACOS ?  -86 :   86;
  EUSERS          = IS_MACOS ?   68 :   87;
  ENOTSOCK        = IS_MACOS ?   38 :   88;
  EDESTADDRREQ    = IS_MACOS ?   39 :   89;
  EMSGSIZE        = IS_MACOS ?   40 :   90;
  EPROTOTYPE      = IS_MACOS ?   41 :   91;
  ENOPROTOOPT     = IS_MACOS ?   42 :   92;
  EPROTONOSUPPORT = IS_MACOS ?   43 :   93;
  ESOCKTNOSUPPORT = IS_MACOS ?   44 :   94;
  ENOTSUPP        = IS_MACOS ?   45 :  -45; // MacOS-only
  EOPNOTSUPP      = IS_MACOS ?  102 :   95;
  EPFNOSUPPORT    = IS_MACOS ?   46 :   96;
  EAFNOSUPPORT    = IS_MACOS ?   47 :   97;
  EADDRINUSE      = IS_MACOS ?   48 :   98;
  EADDRNOTAVAIL   = IS_MACOS ?   49 :   99;
  ENETDOWN        = IS_MACOS ?   50 :  100;
  ENETUNREACH     = IS_MACOS ?   51 :  101;
  ENETRESET       = IS_MACOS ?   52 :  102;
  ECONNABORTED    = IS_MACOS ?   53 :  103;
  ECONNRESET      = IS_MACOS ?   54 :  104;
  ENOBUFS         = IS_MACOS ?   55 :  105;
  EISCONN         = IS_MACOS ?   56 :  106;
  ENOTCONN        = IS_MACOS ?   57 :  107;
  ESHUTDOWN       = IS_MACOS ?   58 :  108;
  ETOOMANYREFS    = IS_MACOS ? -109 :  109;
  ETIMEDOUT       = IS_MACOS ?   60 :  110;
  ECONNREFUSED    = IS_MACOS ?   61 :  111;
  // MacOS:
  // 62: ELOOP (35)
  // 63: ENAMETOOLONG (36)
  EHOSTDOWN       = IS_MACOS ?   64 :  112;
  EHOSTUNREACH    = IS_MACOS ?   65 :  113;
  // 66: ENOTEMPTY (39)
  EPROCLIM        = IS_MACOS ?   67 :  -67;
  // 68: EUSERS (87)
  // 69: EDQUOT (112)
  EALREADY        = IS_MACOS ?   37 :  114;
  EINPROGRESS     = IS_MACOS ?   36 :  115;
  ESTALE          = IS_MACOS ?   70 :  116;

  EUCLEAN         = IS_MACOS ? -117 :  117;
  ENOTNAM         = IS_MACOS ? -118 :  118;
  ENAVAIL         = IS_MACOS ? -119 :  119;
  EISNAM          = IS_MACOS ? -120 :  120;
  EREMOTEIO       = IS_MACOS ? -121 :  121;
  EDQUOT          = IS_MACOS ?   69 :  122;

  ENOMEDIUM       = IS_MACOS ? -123 :  123;
  EMEDIUMTYPE     = IS_MACOS ? -124 :  124;

  // MacOS Extensions
  EBADRPC         = IS_MACOS ?   72 :  -72;
  ERPCMISMATCH    = IS_MACOS ?   73 :  -73;
  EPROGUNAVAIL    = IS_MACOS ?   74 :  -74;
  EPROGMISMATCH   = IS_MACOS ?   75 :  -75;
  EPROCUNAVAIL    = IS_MACOS ?   76 :  -76;
  // 77: ENOLCK (37)
  // 78: ENOSYS (38)
  EFTYPE          = IS_MACOS ?   79 :  -79;
  EAUTH           = IS_MACOS ?   80 :  -80;
  ENEEDAUTH       = IS_MACOS ?   81 :  -81;
  EPWROFF         = IS_MACOS ?   82 :  -82;
  EDEVERR         = IS_MACOS ?   83 :  -83;
  // 84: EOVERFLOW (75)
  EBADARCH        = IS_MACOS ?   86 :  -86;
  ESHLIBVERS      = IS_MACOS ?   87 :  -87;
  EBADMACHO       = IS_MACOS ?   88 :  -88;
  ECANCELLED      = IS_MACOS ?   89 :  -89;
  // 90: EIDRM (43)
  // 91: ENOMSG (42)
  // 92: EILSEQ (84)
  ENOATTR         = IS_MACOS ?   93 :  -93;
}

<<__Memoize>>
function get_throw_errorcode_impl(
): (function(OS\ErrorCode, string): noreturn) {
  $single_code = keyset[
    OS\ChildProcessException::class,
    OS\ConnectionAbortedException::class,
    OS\ConnectionRefusedException::class,
    OS\ConnectionResetException::class,
    OS\AlreadyExistsException::class,
    OS\NotFoundException::class,
    OS\IsADirectoryException::class,
    OS\IsNotADirectoryException::class,
    OS\ProcessLookupException::class,
    OS\TimeoutError::class,
  ];
  $multiple_codes = keyset[
    OS\BrokenPipeException::class,
    OS\PermissionException::class,
  ];

  $throws = new \HH\Lib\Ref(dict[]);
  $add_code = (OS\ErrorCode $code, (function(string): noreturn) $impl) ==> {
    invariant(
      !C\contains_key($throws->value, $code),
      '%s has multiple exception implementations',
      $code,
    );
    $throws->value[$code] = $impl;
  };

  foreach ($single_code as $class) {
    $code = $class::_getValidErrorCode();
    $add_code($code, $msg ==> {
      throw new $class($msg);
    });
  }
  foreach ($multiple_codes as $class) {
    foreach ($class::_getValidErrorCodes() as $code) {
      $add_code($code, $msg ==> {
        throw new $class($code, $msg);
      });
    }
  }

  $throws = $throws->value;

  return ($code, $message) ==> {
    $override = $throws[$code] ?? null;
    if ($override) {
      $override($message);
    }
    throw new OS\Exception($code, $message);
  };
}

function throw_errorcode(OS\ErrorCode $code, string $message): noreturn {
  $impl = get_throw_errorcode_impl();
  $impl($code, $message);
}

function throw_errno(int $errno, string $caller): noreturn {
  invariant(
    $errno !== 0,
    "Asked to throw an errno after %s(), but errno indicates success",
    $caller,
  );
  $name = Errno::getNames()[$errno as Errno];
  $code = OS\ErrorCode::getValues()[$name];
  throw_errorcode($code, Str\format("%s() failed with %s", $caller, $name));
}
