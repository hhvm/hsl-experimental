<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental {
  namespace _Private\_OS {
    newtype PID as arraykey = int;
    newtype ExitCode as int = int;

    /**
     * An abitrary file descriptor number between (STDERR_FILENO, OPEN_MAX).
     *
     * The fd is a writable pipe passed to the child process to detect the
     * termination of the child process. The `Process` class internally read
     * the associated readable pipe in `getExitCodeAsync`. When the child
     * process is terminated, the writable pipe is expected to be closed by
     * the OS, and `getExitCodeAsync` get notified from the readable pipe.
     */
    const int CLOSE_NOTIFIER_FILENO = 42;
  }

  namespace OS {
    use namespace HH\Lib\{C, Dict, IO, OS, Regex, Str, Vec};
    use namespace HH\Lib\Experimental\_Private\_OS;

    final class Process implements IO\CloseableHandle {
      const type TForkAndExecOptions =
        \HH\Lib\_Private\_OS\ForkAndExecveOptions;
      const type TProcessID = _OS\PID;
      const type TExitCode = _OS\ExitCode;
      private function __construct(
        private self::TProcessID $pid,
        private IO\CloseableReadFDHandle $closeNotifier,
      ) {}

      public function getPID(): self::TProcessID {
        return $this->pid;
      }

      <<__Memoize>>
      public async function getExitCodeAsync(): Awaitable<self::TExitCode> {
        await $this->closeNotifier->readAllAsync();
        $status = null;
        \pcntl_waitpid($this->pid, inout $status);
        return \pcntl_wexitstatus($status);
      }

      public function close(): void {
        $status = null;
        switch (
          \pcntl_waitpid($this->pid, inout $status, \WNOHANG | \WUNTRACED)
        ) {
          case 0:
            // The process has not been exited. Kill it.
            \posix_kill($this->pid, \SIGKILL);
            \pcntl_waitpid($this->pid, inout $status, \WUNTRACED);
            break;
          default:
            // No clean up needed because the process has been exited
            break;
        }
        $this->closeNotifier->close();
      }

      <<__ReturnDisposable>>
      final public function closeWhenDisposed(): \IDisposable {
        return new \HH\Lib\_Private\_IO\CloseWhenDisposed($this);
      }

      public static function forkAndExec(
        vec<string> $argv,
        dict<string, string> $environment_variable = dict[],
        ?IO\ReadFDHandle $stdin = null,
        ?IO\WriteFDHandle $stdout = null,
        ?IO\WriteFDHandle $stderr = null,
        this::TForkAndExecOptions $options = shape(),
        string $path = $argv[0],
      ): Process {
        list($read, $write) = IO\pipe();
        using $write->closeWhenDisposed();
        $fds = dict[
          \HH\Lib\_Private\_OS\STDIN_FILENO =>
            $stdin?->getFileDescriptor() ?? OS\stdin(),
          \HH\Lib\_Private\_OS\STDOUT_FILENO =>
            $stdout?->getFileDescriptor() ?? OS\stdout(),
          \HH\Lib\_Private\_OS\STDERR_FILENO =>
            $stderr?->getFileDescriptor() ?? OS\stderr(),
          _OS\CLOSE_NOTIFIER_FILENO => $write->getFileDescriptor(),
        ];

        return new Process(
          \HH\Lib\_Private\_OS\fork_and_execve(
            $path,
            $argv,
            Vec\map_with_key($environment_variable, ($k, $v) ==> {
              // In the shell command language, a word consisting solely of
              // underscores, digits, and alphabetics from the portable character
              // set. The first character of a name is not a digit.
              // See https://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap03.html#tag_03_235
              invariant(
                Regex\matches($k, re"/[a-zA-Z_][a-zA-Z0-9_]*/"),
                'Illegal environment variable name: %s',
                $k,
              );
              return $k.'='.$v;
            }),
            $fds,
            $options,
          ),
          $read,
        );
      }
    }
  }
}
