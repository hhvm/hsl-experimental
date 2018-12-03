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

/** Indicates that an invalid handle was used or requested.
 *
 * For example, calling `IO\requestError()` throws this exception in an
 * HTTP request, as the model only defines input and output streams, not an
 * error stream.
 */
final class InvalidHandleException extends \Exception {}
