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

use namespace HH\Lib\{C, OS, Str};

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
 *
 * Constants are defined in this namespace by the runtime, but currently only
 * if they are defined on all supported platforms; in this enum we manually
 * specify the non-portable ones for now.
 */
enum Errno: int as int {
  /* SUCCESS = 0 */
  EPERM           = EPERM;
  ENOENT          = ENOENT;
  ESRCH           = ESRCH;
  EINTR           = EINTR;
  EIO             = EIO;
  ENXIO           = ENXIO;
  E2BIG           = E2BIG;
  ENOEXEC         = ENOEXEC;
  EBADF           = EBADF;
  ECHILD          = ECHILD;
  EAGAIN          = EAGAIN;
  ENOMEM          = ENOMEM;
  EACCES          = EACCES;
  EFAULT          = EFAULT;
  ENOTBLK         = ENOTBLK;
  EBUSY           = EBUSY;
  EEXIST          = EEXIST;
  EXDEV           = EXDEV;
  ENODEV          = ENODEV;
  ENOTDIR         = ENOTDIR;
  EISDIR          = EISDIR;
  EINVAL          = EINVAL;
  ENFILE          = ENFILE;
  EMFILE          = EMFILE;
  ENOTTY          = ENOTTY;
  ETXTBSY         = ETXTBSY;
  EFBIG           = EFBIG;
  ENOSPC          = ENOSPC;
  ESPIPE          = ESPIPE;
  EROFS           = EROFS;
  EMLINK          = EMLINK;
  EPIPE           = EPIPE;
  EDOM            = EDOM;
  ERANGE          = ERANGE;
  EDEADLK         = EDEADLK;
  ENAMETOOLONG    = ENAMETOOLONG;
  ENOLCK          = ENOLCK;
  ENOSYS          = ENOSYS;
  ENOTEMPTY       = ENOTEMPTY;
  ELOOP           = ELOOP;
  /* EWOULDBLOCK = EAGAIN */
  ENOMSG          = ENOMSG;
  EIDRM           = EIDRM;

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
  ENOSTR          = ENOSTR;
  ENODATA         = ENODATA;
  ETIME           = ETIME;
  ENOSR           = ENOSR;
  ENONET          = IS_MACOS ?  -64 :   64;
  ENOPKG          = IS_MACOS ?  -65 :   65;
  EREMOTE         = IS_MACOS ?  -66 :   66;
  ENOLINK         = ENOLINK;
  EADV            = IS_MACOS ?  -68 :   68;
  ESRMNT          = IS_MACOS ?  -69 :   69;
  ECOMM           = IS_MACOS ?  -70 :   70;
  EPROTO          = EPROTO;
  EMULTIHOP       = EMULTIHOP;
  EDOTDOT         = IS_MACOS ?  -73 :   73;
  EBADMSG         = EBADMSG;
  EOVERFLOW       = EOVERFLOW;
  ENOTUNIQ        = IS_MACOS ?  -76 :   76;
  EBADFD          = IS_MACOS ?  -77 :   77;
  EREMCHG         = IS_MACOS ?  -78 :   78;

  ELIBACC         = IS_MACOS ?  -79 :   79;
  ELIBBAD         = IS_MACOS ?  -80 :   80;
  ELIBSCN         = IS_MACOS ?  -81 :   81;
  ELIBMAX         = IS_MACOS ?  -82 :   82;
  ELIBEXEC        = IS_MACOS ?  -83 :   83;

  EILSEQ          = EILSEQ;
  ERESTART        = IS_MACOS ?  -85 :   85;
  ESTRPIPE        = IS_MACOS ?  -86 :   86;
  EUSERS          = EUSERS;
  ENOTSOCK        = ENOTSOCK;
  EDESTADDRREQ    = EDESTADDRREQ;
  EMSGSIZE        = EMSGSIZE;
  EPROTOTYPE      = EPROTOTYPE;
  ENOPROTOOPT     = ENOPROTOOPT;
  EPROTONOSUPPORT = EPROTONOSUPPORT;
  ESOCKTNOSUPPORT = ESOCKTNOSUPPORT;
  ENOTSUP         = ENOTSUP;
  EOPNOTSUP       = ENOTSUP;
  EPFNOSUPPORT    = EPFNOSUPPORT;
  EAFNOSUPPORT    = EAFNOSUPPORT;
  EADDRINUSE      = EADDRINUSE;
  EADDRNOTAVAIL   = EADDRNOTAVAIL;
  ENETDOWN        = ENETDOWN;
  ENETUNREACH     = ENETUNREACH;
  ENETRESET       = ENETRESET;
  ECONNABORTED    = ECONNABORTED;
  ECONNRESET      = ECONNRESET;
  ENOBUFS         = ENOBUFS;
  EISCONN         = EISCONN;
  ENOTCONN        = ENOTCONN;
  ESHUTDOWN       = ESHUTDOWN;
  ETOOMANYREFS    = IS_MACOS ? -109 :  109;
  ETIMEDOUT       = ETIMEDOUT;
  ECONNREFUSED    = ECONNREFUSED;
  // MacOS:
  // 62: ELOOP (35)
  // 63: ENAMETOOLONG (36)
  EHOSTDOWN       = EHOSTDOWN;
  EHOSTUNREACH    = EHOSTUNREACH;
  // 66: ENOTEMPTY (39)
  EPROCLIM        = IS_MACOS ?   67 :  -67;
  // 68: EUSERS (87)
  // 69: EDQUOT (112)
  EALREADY        = EALREADY;
  EINPROGRESS     = EINPROGRESS;
  ESTALE          = ESTALE;

  EUCLEAN         = IS_MACOS ? -117 :  117;
  ENOTNAM         = IS_MACOS ? -118 :  118;
  ENAVAIL         = IS_MACOS ? -119 :  119;
  EISNAM          = IS_MACOS ? -120 :  120;
  EREMOTEIO       = IS_MACOS ? -121 :  121;
  EDQUOT          = EDQUOT;

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
