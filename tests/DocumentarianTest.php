<?php

use Illuminate\Support\Collection;
use Mpociot\Documentarian\Documentarian;
use PHPUnit\Framework\TestCase;

function glob_recursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
    }

    return $files;
}


class DocumentarianTest extends TestCase
{

    public function tearDown(): void
    {
        exec('rm -rf ' . __DIR__ . '/output');
        mkdir(__DIR__ . '/output');
        touch(__DIR__ . '/output/.gitkeep');

        parent::tearDown();
    }

    public function test_creates_documentation_folder_and_copies_assets()
    {
        $outputDir = __DIR__ . '/output';

        $documentarian = new Documentarian();
        $documentarian->create($outputDir);

        // Test for folders

        $this->assertTrue(is_dir($outputDir . '/source'));
        $this->assertTrue(is_dir($outputDir . '/source/includes'));
        $this->assertTrue(is_dir($outputDir . '/source/assets'));
        $this->assertTrue(is_dir($outputDir . '/css'));
        $this->assertTrue(is_dir($outputDir . '/js'));

        // Test that stubbed files exist
        $this->assertFileExists($outputDir . '/source/index.md');
        $this->assertFileExists($outputDir . '/source/.gitignore');
        $this->assertFileExists($outputDir . '/source/includes/_errors.md');
        $this->assertFileExists($outputDir . '/source/package.json');
        $this->assertFileExists($outputDir . '/source/gulpfile.js');
        $this->assertFileExists($outputDir . '/source/config.php');
        $this->assertFileExists($outputDir . '/js/all.js');
        $this->assertFileExists($outputDir . '/css/style.css');

        // Test that resources were copied
        $jsFiles = glob_recursive(__DIR__ . '/../resources/js/*');
        foreach ($jsFiles as $jsFile) {
            $file = str_replace(__DIR__ . '/../resources/', $outputDir . '/source/assets/', $jsFile);
            if (!is_dir($jsFile)) {
                $this->assertFileExists($file);
            }
        }

        $cssFiles = glob_recursive(__DIR__ . '/../resources/stylus/*');
        foreach ($cssFiles as $cssFile) {
            $file = str_replace(__DIR__ . '/../resources/', $outputDir . '/source/assets/', $cssFile);
            if (!is_dir($cssFile)) {
                $this->assertFileExists($file);
            }
        }

        $imageFiles = glob_recursive(__DIR__ . '/../resources/images/*');
        foreach ($imageFiles as $imageFile) {
            $file = str_replace(__DIR__ . '/../resources/', $outputDir . '/source/assets/', $imageFile);
            if (!is_dir($imageFile)) {
                $this->assertFileExists($file);
            }
        }
    }

    public function test_cannot_generate_html_if_folder_does_not_exist()
    {
        $outputDir = __DIR__ . '/output';

        $documentarian = new Documentarian();
        $this->assertFalse($documentarian->generate($outputDir));
    }

    public function test_can_generate_html()
    {
        $outputDir = __DIR__ . '/output';
        $assertionDir = __DIR__ . '/assertions';

        $documentarian = new Documentarian();
        $documentarian->create($outputDir);

        // test1.md - no frontmatter yaml present
        copy(__DIR__ . '/files/test1.md', $outputDir . '/source/index.md');
        $documentarian->generate($outputDir);
        $this->assertFileEquals($assertionDir . '/test1.html', $outputDir . '/index.html');

        // test2.md - valid frontmatter yaml
        copy(__DIR__ . '/files/test2.md', $outputDir . '/source/index.md');
        $documentarian->generate($outputDir);
        $this->assertFileEquals($assertionDir . '/test2.html', $outputDir . '/index.html');

        // test3.md - include and parse additional markdown files
        copy(__DIR__ . '/files/test3.md', $outputDir . '/source/index.md');
        $documentarian->generate($outputDir);
        $this->assertFileEquals($assertionDir . '/test3.html', $outputDir . '/index.html');

        // test4.md - ignore not existing include file
        copy(__DIR__ . '/files/test4.md', $outputDir . '/source/index.md');
        $documentarian->generate($outputDir);
        $this->assertFileEquals($assertionDir . '/test4.html', $outputDir . '/index.html');

    }

    public function test_can_get_config_value()
    {
        $outputDir = __DIR__ . '/output';

        $documentarian = new Documentarian();
        $documentarian->create($outputDir);
        
        $this->assertTrue(is_array($documentarian->config($outputDir)));
        $this->assertEquals('git', $documentarian->config($outputDir, 'deployment.type'));
        $this->assertEquals('gh-pages', $documentarian->config($outputDir, 'deployment.branch'));
    }

}
