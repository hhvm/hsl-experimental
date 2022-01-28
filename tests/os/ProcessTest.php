<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */
use type Facebook\HackTest\HackTest;
use type HH\Lib\Experimental\OS\Process;
use namespace HH\Lib\IO;
use function Facebook\FBExpect\expect;

final class ProcessTest extends HackTest {
  public async function testBasicUsage(): Awaitable<void> {
    list($read_pipe, $write_pipe) = IO\pipe();
    using $read_pipe->closeWhenDisposed();
    try {
      $process = Process::forkAndExec(
        vec["/usr/bin/env", "ls", "src/os"],
        dict[],
        null,
        $write_pipe,
      );
    } finally {
      $write_pipe->close();
    }
    using $process->closeWhenDisposed();
    $output = await $read_pipe->readAllAsync();
    expect(await $process->getExitCodeAsync())->toEqual(0);
    expect($output)->toContainSubstring("Process.php");
  }

  public async function testPipeUsage(): Awaitable<void> {
    list($final_read_pipe, $ls_write_pipe) = IO\pipe();
    list($ls_read_pipe, $find_write_pipe) = IO\pipe();

    try {
      $find_process = Process::forkAndExec(
        vec["/usr/bin/env", "find", "src/os", "-type", "f", "-print0"],
        dict[],
        null,
        $find_write_pipe,
      );
    } finally {
      $find_write_pipe->close();
    }
    using $find_process->closeWhenDisposed();

    try {
      $cat_process = Process::forkAndExec(
        vec["/usr/bin/env", "xargs", "-0", "cat"],
        dict[],
        $ls_read_pipe,
        $ls_write_pipe,
      );
    } finally {
      $ls_read_pipe->close();
      $ls_write_pipe->close();
    }
    using $cat_process->closeWhenDisposed();
    expect(await $find_process->getExitCodeAsync())->toEqual(0);
    expect(await $cat_process->getExitCodeAsync())->toEqual(0);
    $output = await $final_read_pipe->readAllAsync();
    expect($output)->toContainSubstring("class Process");
  }
}
