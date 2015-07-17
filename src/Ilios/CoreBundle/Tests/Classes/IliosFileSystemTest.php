<?php
namespace Ilios\CoreBundle\Tests\Classes;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mockery as m;
use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;

use Ilios\CoreBundle\Classes\IliosFileSystem;

class IliosFileSystemTest extends TestCase
{
    /**
     *
     * @var IliosFileSystem
     */
    private $iliosFileSystem;
    
    /**
     * Mock File System
     * @var FileSystem
     */
    private $mockFileSystem;
    
    /**
     * @var string
     */
    private $fakeTestFileDir;
    
    public function setUp()
    {
        $this->fakeTestFileDir = realpath(__DIR__ . '/FakeTestFiles');
        
        $this->mockFileSystem = m::mock('Symfony\Component\Filesystem\Filesystem');
        $this->mockFileSystem->shouldReceive('exists')->with($this->fakeTestFileDir)->andReturn(true);
        
        $this->iliosFileSystem = new IliosFileSystem($this->mockFileSystem, $this->fakeTestFileDir);
    }

    public function tearDown()
    {
        unset($this->mockFileSystem);
        unset($this->iliosFileSystem);
        m::close();
    }

    public function testStoreLeaningMaterialFile()
    {
        $path = __FILE__;
        $newFilePath = $this->getTestFilePath($path);
        $file = m::mock('Symfony\Component\HttpFoundation\File\File')
            ->shouldReceive('getPathname')->andReturn($path)->getMock();
        $this->mockFileSystem->shouldReceive('copy')
            ->with($path, $newFilePath);
        $this->mockFileSystem->shouldReceive('mkdir');
        $this->iliosFileSystem->storeLearningMaterialFile($file);
    }

    public function testStoreLeaningMaterialFileReplaceFile()
    {
        $path = __FILE__;
        $newFilePath = $this->getTestFilePath($path);
        $file = m::mock('Symfony\Component\HttpFoundation\File\File')
            ->shouldReceive('getPathname')->andReturn($path)->getMock();
        $this->mockFileSystem->shouldReceive('exists')
            ->with($newFilePath)->andReturn(false);
        $this->mockFileSystem->shouldReceive('rename')
            ->with($path, $newFilePath);
        $this->mockFileSystem->shouldReceive('mkdir');
        $this->iliosFileSystem->storeLearningMaterialFile($file, false);
    }

    public function testStoreLeaningMaterialFileDontReplaceFileIfExists()
    {
        $path = __FILE__;
        $newFilePath = $this->getTestFilePath($path);
        $file = m::mock('Symfony\Component\HttpFoundation\File\File')
            ->shouldReceive('getPathname')->andReturn($path)->getMock();
        $this->mockFileSystem->shouldReceive('exists')
            ->with($newFilePath)->andReturn(true);
        $this->mockFileSystem->shouldReceive('mkdir');
        $this->iliosFileSystem->storeLearningMaterialFile($file, false);
    }

    public function testGetLearningMaterialFilePath()
    {
        $path = __FILE__;
        $file = m::mock('Symfony\Component\HttpFoundation\File\File')
            ->shouldReceive('getPathname')->andReturn($path)->getMock();
        $newPath = $this->iliosFileSystem->getLearningMaterialFilePath($file);
        $this->assertSame($this->fakeTestFileDir . '/' . $newPath, $this->getTestFilePath($path));
    }

    public function testRemoveFile()
    {
        $file = 'foojunk';
        $this->mockFileSystem->shouldReceive('remove')->with($this->fakeTestFileDir . '/' . $file);
        $this->iliosFileSystem->removeFile($file);
    }

    public function testGetFile()
    {
        $fs = new SymfonyFileSystem();
        $someJunk = 'whatever dude';
        $hash = md5($someJunk);
        $hashDirectory = substr($hash, 0, 2);
        $parts = [
            $this->fakeTestFileDir,
            'learning_materials',
            'lm',
            $hashDirectory
        ];
        $dir = implode($parts, '/');
        $fs->mkdir($dir);
        $testFilePath = $dir . '/' . $hash;
        file_put_contents($testFilePath, $someJunk);
        $file = m::mock('Symfony\Component\HttpFoundation\File\File')
            ->shouldReceive('getPathname')->andReturn($testFilePath)->getMock();
        $this->mockFileSystem->shouldReceive('exists')
            ->with($testFilePath)->andReturn(true);
        $this->mockFileSystem->shouldReceive('mkdir');
        $newPath = $this->iliosFileSystem->storeLearningMaterialFile($file, false);
        
        $newFile = $this->iliosFileSystem->getFile($newPath);
        $this->assertSame($newFile->getPathname(), $testFilePath);
        $this->assertSame(file_get_contents($newFile->getPathname()), $someJunk);
        $fs->remove($this->fakeTestFileDir . '/learning_materials');
    }
    
    
    protected function getTestFilePath($path)
    {
        $hash = md5_file($path);
        $hashDirectory = substr($hash, 0, 2);
        $parts = [
            $this->fakeTestFileDir,
            'learning_materials',
            'lm',
            $hashDirectory,
            $hash
        ];
        return implode($parts, '/');
    }
}
