<?php

declare(strict_types=1);

namespace Tests\Support;

use FilesystemIterator;
use Mockery;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Support\Contracts\ConfigInterface;
use Support\Debug\Repository\FileStore;

trait FileRepositoryTransaction
{
    private const string FILE_DIR = __DIR__ . '/../../storage/app/tests';

    protected function setUp(): void
    {
        parent::setUp();

        $config = Mockery::mock(ConfigInterface::class);

        $directory = $this->getDirectoryName();

        $config->shouldReceive('getString')
            ->with('debug.file.path')
            ->andReturn($directory);

        $this->app->bind(ConfigInterface::class, fn () => $config);

        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteRecursive($this->getDirectoryName());
    }

    private function deleteRecursive(string $directory): void
    {
        /** @var SplFileInfo $file */
        foreach (
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            ) as $file
        ) {
            unlink($file->getRealPath());
        }

        if (file_exists($directory)) {
            rmdir($directory);
        }
    }

    private function getDirectoryName(): string
    {
        return self::FILE_DIR . '/' . $this->name();
    }

    /**
     * @template T
     *
     * @param class-string<T> $repository
     * @param T               $value
     */
    private function factory(string $repository, int|string $key, mixed $value): void
    {
        $fileName = new ReflectionClass($repository)->getConstant('FILE_NAME');

        if ($fileName === false || $fileName === '') {
            throw new RuntimeException('リポジトリに FILE_NAME 定数が設定されていません。');
        }

        /** @var FileStore $store */
        $store = $this->app->make(FileStore::class);

        $store->put($this->getDirectoryName() . '/' . $fileName, $key, $value);
    }
}
