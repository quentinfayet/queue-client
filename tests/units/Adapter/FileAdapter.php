<?php

namespace ReputationVIP\QueueClient\tests\units\Adapter;

use ArrayIterator;
use mageekguy\atoum;
use ReputationVIP\QueueClient\PriorityHandler\ThreeLevelPriorityHandler;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ReputationVIP\QueueClient\Utils\LockHandlerFactory;

class MockIOExceptionInterface extends \Exception implements IOExceptionInterface {

    public function getPath()
    {
        return '';
    }
};

class FileAdapter extends atoum\test
{
    public function testFileAdapterClass()
    {
        $this->testedClass->implements('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapter__construct()
    {
        $this->object($this->newTestedInstance('/tmp/test/'));
    }

    public function testFileAdapter__constructWithFilesystemError(Filesystem $fs, Finder $finder, LockHandlerFactory $lockHandlerFactory)
    {
        $this->exception(function () use($fs, $finder, $lockHandlerFactory) {
                $this->newTestedInstance('', null, $fs, $finder, $lockHandlerFactory);
            });

        $this->calling($fs)->mkdir->throw = new MockIOExceptionInterface;
        $this->calling($fs)->exists = false;

        $this->exception(function () use($fs, $finder, $lockHandlerFactory) {
                $this->newTestedInstance('/tmp/test/', null, $fs, $finder, $lockHandlerFactory);
            });
    }

    public function testFileAdapterDeleteQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $this->calling($mockFs)->exists = true;
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->calling($mockLockHandlerFactory)->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $this->given($FileAdapter)
            ->class($FileAdapter->deleteQueue('testQueue'))->hasInterface('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapterDeleteQueueWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteQueue('');
        });
    }

    public function testFileAdapterDeleteQueueWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $this->calling($mockFs)->exists = false;
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->calling($mockLockHandlerFactory)->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteQueue('testQueue');
        });
    }

    public function testFileAdapterDeleteQueueWithLockFailed()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->calling($mockFs)->exists = true;
        $this->calling($mockLockHandlerFactory)->getLockHandler =  function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteQueue('testQueue');
        });
    }

    public function testFileAdapterCreateQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFs->getMockController()->exists = false;
        $this->given($FileAdapter)
            ->class($FileAdapter->createQueue('testQueue'))->hasInterface('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapterCreateQueueWithFsException()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFs->getMockController()->exists = false;
        $mockFs->getMockController()->dumpFile = function($repository) {
            throw new \Exception('test exception');
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->createQueue('testQueue');
        });
    }

    public function testFileAdapterCreateQueueWithLockFailed()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $mockFs->getMockController()->exists = false;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->createQueue('testQueue');
        });
    }

    public function testFileAdapterCreateQueueWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->createQueue('');
        });
    }

    public function testFileAdapterCreateQueueWithExistingQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->createQueue('testQueue');
        });
    }

    public function testFileAdapterCreateQueueWithSpaceIngQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = false;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->createQueue('test Queue');
        });
    }

    public function testFileAdapterPurgeQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->given($FileAdapter)
            ->class($FileAdapter->purgeQueue('testQueue'))->hasInterface('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapterPurgeQueueWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = false;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->purgeQueue('testQueue');
        });
    }

    public function testFileAdapterPurgeQueueWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->purgeQueue('');
        });
    }

    public function testFileAdapterPurgeQueueWithLockFailed() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->purgeQueue('testQueue');
        });
    }

    public function testFileAdapterPurgeQueueWithEmptyQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return ''; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->purgeQueue('testQueue');
        });
    }

    public function testFileAdapterPurgeQueueWithBadQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"bad":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->purgeQueue('testQueue');
        });
    }

    public function testFileAdapterIsEmptyWithEmptyQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this
            ->given($FileAdapter)
            ->boolean($FileAdapter->isEmpty('testQueue'))
            ->isTrue();
    }

    public function testFileAdapterIsEmptyWithNoEmptyQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[{"id":"testQueue-HIGH559f77704e87c5.40358915","time-in-flight":null,"Body":"s:12:\"Test message\";"}]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this
            ->given($FileAdapter)
            ->boolean($FileAdapter->isEmpty('testQueue'))
            ->isFalse();
    }

    public function testFileAdapterIsEmptyWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->isEmpty('');
        });
    }

    public function testFileAdapterIsEmptyWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = false;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->isEmpty('testQueue');
        });
    }

    public function testFileAdapterIsEmptyWithEmptyQueueContent()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return ''; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->isEmpty('testQueue');
        });
    }

    public function testFileAdapterIsEmptyWithBadQueueContent()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"bad":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->isEmpty('testQueue');
        });
    }

    public function testFileAdapterListQueues()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testOneQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
                $files[] = 'prefixTestTwoQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
                $files[] = 'testTwoQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
                $files[] = 'testThreeQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this
            ->given($FileAdapter)
            ->array($FileAdapter->listQueues())
            ->containsValues(['testOneQueue', 'testTwoQueue', 'testThreeQueue']);
    }

    public function testFileAdapterListQueuesWithPrefix()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testOneQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
                $files[] = 'prefixTestTwoQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
                $files[] = 'testTwoQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
                $files[] = 'prefixTestOneQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this
            ->given($FileAdapter)
            ->array($FileAdapter->listQueues('prefix'))
            ->containsValues(['prefixTestOneQueue', 'prefixTestTwoQueue']);
    }

    public function testFileAdapterListQueuesWithEmptyQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFinder->getMockController()->getIterator = function () {
            return new ArrayIterator([]);
        };
        $this
            ->given($FileAdapter)
            ->array($FileAdapter->listQueues())
            ->isEmpty();
    }

    public function testFileAdapterAddMessage()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->given($FileAdapter)
            ->class($FileAdapter->addMessage('testQueue', 'test Message one'))->hasInterface('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapterAddMessageWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->addMessage('', '');
        });
    }

    public function testFileAdapterAddMessageWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $mockFs->getMockController()->exists = false;
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->addMessage('testQueue', '');
        });
    }

    public function testFileAdapterAddMessageWithEmptyMessage()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
                $FileAdapter->addMessage('testQueue', '');
        });
    }

    public function testFileAdapterAddMessageLockFailed() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->addMessage('testQueue', 'test message');
        });
    }

    public function testFileAdapterAddMessageWithEmptyQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return ''; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->addMessage('testQueue', 'test message');
        });
    }

    public function testFileAdapterAddMessageWithBadQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"bad":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->addMessage('testQueue', 'test message');
        });
    }

    public function testFileAdapterGetNumberMessagesWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getNumberMessages('');
        });
    }

    public function testFileAdapterGetNumberMessagesWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $mockFs->getMockController()->exists = false;
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getNumberMessages('testQueue');
        });
    }

    public function testFileAdapterGetNumberMessagesLockFailed() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getNumberMessages('testQueue');
        });
    }

    public function testFileAdapterGetNumberMessagesWithEmptyQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return ''; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getNumberMessages('testQueue');
        });
    }

    public function testFileAdapterGetNumberMessagesWithBadQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"bad":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getNumberMessages('testQueue');
        });
    }

    public function testFileAdapterGetNumberMessages() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[{"id":"testQueue-HIGH559f77704e87c5.40358915","time-in-flight":null,"Body":"s:12:\"Test message\";"},{"id":"testQueue-HIGH559f9a97733a01.98514574","time-in-flight":null,"Body":"s:16:\"test message two\";"}]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->given($FileAdapter)
            ->integer($FileAdapter->getNumberMessages('testQueue'))->isEqualTo(6);
    }

    public function testFileAdapterGetMessagesWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('', 1);
        });
    }

    public function testFileAdapterGetMessagesWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $mockFs->getMockController()->exists = false;
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue', 1);
        });
    }

    public function testFileAdapterAddMessagesWithNoNumericNbrMsg()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue', 'toto');
        });
    }

    public function testFileAdapterGetMessagesWithNotValidNumericNbrMsg()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue', -5);
        });
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue', (\ReputationVIP\QueueClient\Adapter\FileAdapter::MAX_NB_MESSAGES + 1));
        });
    }

    public function testFileAdapterGetMessagesLockFailed() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue');
        });
    }

    public function testFileAdapterGetMessagesWithEmptyQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return ''; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue');
        });
    }

    public function testFileAdapterGetMessagesWithBadQueueContent() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"bad":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->getMessages('testQueue');
        });
    }

    public function testFileAdapterGetMessages() {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[{"id":"testQueue-HIGH559f77704e87c5.40358915","time-in-flight":null,"Body":"s:12:\"Test message\";"},{"id":"testQueue-HIGH559f9a97733a01.98514574","time-in-flight":null,"Body":"s:16:\"test message two\";"}]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->given($FileAdapter)
            ->array($FileAdapter->GetMessages('testQueue', 6));
        $this->given($FileAdapter)
            ->array($FileAdapter->GetMessages('testQueue', 8));
    }

    public function testFileAdapterDeleteMessageWithEmptyQueueName()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteMessage('', []);
        });
    }

    public function testFileAdapterDeleteMessageWithNoQueueFile()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = false;
        $this->exception(function() use($FileAdapter, $priorityHandler) {
            $FileAdapter->deleteMessage('testQueue', ['id' => 'testQueue-HIGH559f77704e87c5.40358915', 'priority' => $priorityHandler->getHighest()]);
        });
    }

    public function testFileAdapterDeleteMessageWithNoMessage()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = false;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteMessage('testQueue', []);
        });
    }

    public function testFileAdapterDeleteMessageWithNoIdField()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $this->exception(function() use($FileAdapter, $priorityHandler) {
            $FileAdapter->deleteMessage('testQueue', ['priority' => $priorityHandler->getHighest()]);
        });
    }

    public function testFileAdapterDeleteMessageWithNotPriorityField()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteMessage('testQueue', ['id' => 'testQueue-HIGH559f77704e87c5.40358915']);
        });
    }

    public function testFileAdapterDeleteMessageWithBadMessageType()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->deleteMessage('testQueue', 'message');
        });
    }

    public function testFileAdapterDeleteMessageLockFailed()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = false;
            return $mockLockHandler;
        };
        $this->exception(function() use($FileAdapter, $priorityHandler) {
            $FileAdapter->deleteMessage('testQueue', ['id' => 'testQueue-HIGH559f77704e87c5.40358915', 'priority' => $priorityHandler->getHighest()]);
        });
    }

    public function testFileAdapterDeleteMessageWithEmptyQueueContent()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return ''; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter, $priorityHandler) {
            $FileAdapter->deleteMessage('testQueue', ['id' => 'testQueue-HIGH559f77704e87c5.40358915', 'priority' => $priorityHandler->getHighest()]);
        });
    }

    public function testFileAdapterDeleteMessageWithBadQueueContent()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"bad":[]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->exception(function() use($FileAdapter, $priorityHandler) {
            $FileAdapter->deleteMessage('testQueue', ['id' => 'testQueue-HIGH559f77704e87c5.40358915', 'priority' => $priorityHandler->getHighest()]);
        });
    }

    public function testFileAdapterDeleteMessage()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = true;
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[{"id":"testQueue-HIGH559f77704e87c5.40358915","time-in-flight":null,"Body":"s:12:\"Test message\";"},{"id":"testQueue-HIGH559f9a97733a01.98514574","time-in-flight":null,"Body":"s:16:\"test message two\";"}]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->given($FileAdapter)
            ->class($FileAdapter->deleteMessage('testQueue', array('id' => 'testQueue-HIGH559f77704e87c5.40358915', 'priority' => $priorityHandler->getHighest())))->hasInterface('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapterRenameQueueWithEmptyParameter()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->renameQueue('', 'newTestQueue');
        });
        $this->exception(function() use($FileAdapter) {
            $FileAdapter->renameQueue('testQueue', '');
        });
    }

    public function testFileAdapterRenameQueue()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $priorityHandler = new ThreeLevelPriorityHandler();
        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', $priorityHandler, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $mockFs->getMockController()->exists = function ($queue) {
            static $i = 0;
            if ($i < 3) {
                $i++;
                return false;
            }
            return true;
        };
        $mockLockHandlerFactory->getMockController()->getLockHandler = function($repository) {
            $mockLockHandler = new \mock\Symfony\Component\Filesystem\LockHandler($repository);
            $mockLockHandler->getMockController()->lock = true;
            return $mockLockHandler;
        };
        $mockFinder->getMockController()->getIterator = function () use ($priorityHandler) {
            $files = [];
            $priorities = $priorityHandler->getAll();
            foreach ($priorities as $priority) {
                $files[] = 'testQueue'.\ReputationVIP\QueueClient\Adapter\FileAdapter::PRIORITY_SEPARATOR.$priority.'.'.\ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION;
            }
            $mocksSplFileInfo = [];
            foreach ($files as $file) {
                $mockSplFileInfo = new \mock\Symfony\Component\Finder\SplFileInfo('', '', '');

                $mockSplFileInfo->getMockController()->getExtension = function () { return \ReputationVIP\QueueClient\Adapter\FileAdapter::QUEUE_FILE_EXTENSION; };
                $mockSplFileInfo->getMockController()->getRelativePathname = function () use($file) { return $file; };
                $mockSplFileInfo->getMockController()->getPathname = function () use($file) { return '/tmp/test/' . $file; };
                $mockSplFileInfo->getMockController()->getContents = function () use($file) { return '{"queue":[{"id":"testQueue-HIGH559f77704e87c5.40358915","time-in-flight":null,"Body":"s:12:\"Test message\";"},{"id":"testQueue-HIGH559f9a97733a01.98514574","time-in-flight":null,"Body":"s:16:\"test message two\";"}]}'; };
                $mocksSplFileInfo[] = $mockSplFileInfo;
            }
            return new ArrayIterator($mocksSplFileInfo);
        };
        $this->given($FileAdapter)
            ->class($FileAdapter->renameQueue('testQueue', 'newTestQueue'))->hasInterface('\ReputationVIP\QueueClient\Adapter\AdapterInterface');
    }

    public function testFileAdapterGetPriorityHandler()
    {
        $mockFs = new \mock\Symfony\Component\Filesystem\Filesystem;
        $mockFinder = new \mock\Symfony\Component\Finder\Finder;
        $mockLockHandlerFactory = new \mock\ReputationVIP\QueueClient\Utils\LockHandlerFactory;

        $FileAdapter = new \ReputationVIP\QueueClient\Adapter\FileAdapter('/tmp/test/', null, $mockFs, $mockFinder, $mockLockHandlerFactory);
        $this->given($FileAdapter)
            ->class($FileAdapter->getPriorityHandler())->hasInterface('\ReputationVIP\QueueClient\PriorityHandler\PriorityHandlerInterface');
    }
}
