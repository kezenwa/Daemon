<?php
namespace Gt\Daemon\Test;

use Gt\Daemon\CommandNotFoundException;
use Gt\Daemon\DaemonException;
use Gt\Daemon\Process;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ProcessTest extends TestCase {
	protected string $tmpBase;

	protected function setUp():void {
		$this->tmpBase = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"test",
			"daemon",
		]);
	}

	public function tearDown():void {
		if(!is_dir($this->tmpBase)) {
			return;
		}

		$directory = new RecursiveDirectoryIterator(
			$this->tmpBase,
			RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
			| RecursiveDirectoryIterator::KEY_AS_PATHNAME
		);
		$iterator = new RecursiveIteratorIterator(
			$directory,
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach($iterator as $file) {
			/** @var SplFileInfo $file */
			if($file->getFilename() === "."
			|| $file->getFilename() === "..") {
				continue;
			}

			if($file->isFile()) {
				unlink($file->getPathname());
			}
			else {
				rmdir($file->getPathname());
			}
		}

		rmdir($this->tmpBase);
	}

	public function testExec():void {
		$tmpFile = implode(DIRECTORY_SEPARATOR, [
			$this->tmpBase,
			uniqid(),
		]);
		if(!is_dir(dirname($tmpFile))) {
			mkdir(dirname($tmpFile), 0775, true);
		}
		$sut = new Process(PHP_BINARY, "-r", "touch(\"$tmpFile\");");

		self::assertFileDoesNotExist($tmpFile);
		$sut->exec();
		while($sut->isRunning()) {
			usleep(100000);
		}

		self::assertFileExists($tmpFile);
	}

	public function testExecFailure():void {
		$sut = new Process("/this/does/not/exist/" . uniqid());
		$sut->setBlocking();

		try {
			$sut->exec();
		}
		catch(DaemonException $exception) {}

		self::assertEquals(127, $sut->getExitCode());
	}

	public function testGetCommand():void {
		$rawCommand = [
			"/path/to/binary",
			"attr1key=attr1value",
			"--name='yes/no'",
		];
		$sut = new Process(...$rawCommand);
		$actualCommand = $sut->getCommand();

		self::assertEquals(
			$rawCommand,
			$actualCommand
		);
	}

	public function testGetOutputNotRunning():void {
		self::expectExceptionMessage("Process is not running");
		$sut = new Process("echo 'test-message'");
		$sut->getOutput();
	}

	public function testGetOutput():void {
		$sut = new Process("echo", "test-message");
		$sut->exec();

		while($sut->isRunning()) {
			usleep(100000);
		}

		$output = $sut->getOutput();
		self::assertEquals("test-message\n", $output);
	}

	public function testExecutingNonExistentCommand():void {
		$sut = new Process("/does/not/exist");

		self::expectException(CommandNotFoundException::class);
		$sut->exec();
	}

	public function testGetExistCodeRunning():void {
		$sut = new Process("sleep", "1");
		$sut->exec();
		self::assertNull($sut->getExitCode());
	}

	public function testGetExitCodeTerminate():void {
		$sut = new Process("echo", "quick");
		$sut->exec();

		while($sut->isRunning()) {
			usleep(100000);
		}

		self::assertEquals(0, $sut->getExitCode());
	}

	public function testGetPidNotRunning():void {
		$sut = new Process("echo", "not running");
		self::assertNull($sut->getPid());
	}

	public function testGetPid():void {
		$sut = new Process("sleep", "1");
		$sut->exec();
		self::assertIsInt($sut->getPid());
	}

	public function testExecBlocking():void {
		$sut = new Process("sleep", "0.1");
		$sut->exec();
		self::assertTrue($sut->isRunning());

		$sut = new Process("sleep", "0.1");
		$sut->setBlocking();
		$sut->exec();
		self::assertFalse($sut->isRunning());
	}
}
