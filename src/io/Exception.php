<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

use namespace HH\Lib\Experimental\OS;

/** Any kind of IO exception.
 *
 * Subclasses will usually implement `OS\IExceptionWithErrno`, but not always;
 * for example, some socket errors will have an `HErrno` instead.
 */
abstract class Exception
  extends \Exception
  implements OS\ExceptionWithNullableErrno {
}
