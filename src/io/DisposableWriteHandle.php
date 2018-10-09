<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO;

use namespace HH\Lib\Experimental\Filesystem;

/* HH_FIXME[4194] non-disposable parent interface t34965102 */
interface DisposableWriteHandle extends WriteHandle, \IAsyncDisposable {
}
