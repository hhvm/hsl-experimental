<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */


namespace HH\Lib\Experimental\IO {
  newtype Handle<-T> = resource;
}

namespace HH\Lib\_Private {
  use namespace HH\Lib\Experimental\{Filesystem, IO};

  function io_handle_from_resource<T as Filesystem\FileBase>(
    classname<T> $_type,
    resource $resource,
  ): IO\Handle<T> {
    $resource_type = \get_resource_type($resource);
    invariant(
      $resource_type === 'stream',
      '"%s" is not a file-like resource',
      $resource_type,
    );
    return $resource;
  }

  function resource_from_io_handle<T>(
    IO\Handle<T> $handle,
  ): resource {
    return $handle;
  }
}
