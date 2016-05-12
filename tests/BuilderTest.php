<?php

/**
 * Class BuilderTest
 */
class BuilderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        parent::tearDown();

        exec("rm -rf " . __DIR__ . '/data/runtime/tmp');
    }

    public function setUp()
    {
        parent::setUp();

        exec("mkdir " . __DIR__ . '/data/runtime/tmp');
    }

    public function testBuilder()
    {
        $release1 = new \upgrade\builder\ReleaseArchive(__DIR__ . '/data/releases/ultimate_4.3.4.tgz', 'ultimate', '4.3.4');
        $release2 = new \upgrade\builder\ReleaseArchive(__DIR__ . '/data/releases/ultimate_4.3.5.tgz', 'ultimate', '4.3.5');

        $builder = new \upgrade\builder\Builder($release1, $release2, __DIR__ . '/data/runtime/tmp');

        $builder->setMigrationsPath(__DIR__ . '/data/upgrade/migrations');
        $builder->setValidatorsPath(__DIR__ . '/data/upgrade/validators');
        $builder->setScriptsPath(__DIR__ . '/data/upgrade/scripts');
        $builder->setExtraFilesPath(__DIR__ . '/data/upgrade/extra_files');
        $builder->setExtraPath(__DIR__ . '/data/upgrade/extra/extra.php');

        $this->assertEquals(
            array(
                __DIR__ . '/data/upgrade/migrations/62312332434_mve_migration3.php',
                __DIR__ . '/data/upgrade/migrations/62312332434_test_migration1.php',
                __DIR__ . '/data/upgrade/migrations/62312332434_test_migration2.php'
            ),
            $builder->getMigrations()
        );

        $this->assertEquals(
            array(
                __DIR__ . '/data/upgrade/validators/validator1.php',
                __DIR__ . '/data/upgrade/validators/validator2.php',
            ),
            $builder->getValidators()
        );

        $this->assertEquals(
            array(
                __DIR__ . '/data/upgrade/extra_files/test.php',
            ),
            $builder->getExtraFiles()
        );

        $this->assertEquals(
            array(
                'extra' => 1
            ),
            $builder->getExtraPackageData()
        );

        $this->assertEquals(__DIR__ . '/data/upgrade/scripts/pre_script.php', $builder->getPreScriptFile());
        $this->assertEquals(__DIR__ . '/data/upgrade/scripts/post_script.php', $builder->getPostScriptFile());

        $builder->unpackArchives();
        $files = $builder->getFiles();

        $this->assertEquals('new', $files['api.php']['status']);
        $this->assertEquals('deleted', $files['app/test.php']['status']);
        $this->assertEquals('changed', $files['app/test1.php']['status']);
        $this->assertEquals('new', $files['changelog.txt']['status']);
        $this->assertEquals('changed', $files['config.php']['status']);
        $this->assertEquals('new', $files['index.php']['status']);
        $this->assertEquals('new', $files['test3.sql']['status']);
        $this->assertEquals('deleted', $files['test2.sql']['status']);
        $this->assertEquals('deleted', $files['robots.txt']['status']);
        $this->assertEquals('deleted', $files['admin.php']['status']);

        $builder->setExcludeFiles(array('config.php'));
        $files = $builder->getFiles();

        $this->assertCount(9, $files);
        $this->assertArrayNotHasKey('config.php', $files);

        $builder->setExcludeFiles(array(), array('app/test.php'));
        $files = $builder->getFiles();

        $this->assertCount(9, $files);
        $this->assertArrayNotHasKey('app/test.php', $files);

        $builder->setExcludeFiles(array());
        $builder->setExcludeExtensions(array('sql'));
        $files = $builder->getFiles();

        $this->assertCount(8, $files);
        $this->assertArrayNotHasKey('test3.sql', $files);
        $this->assertArrayNotHasKey('test2.sql', $files);
    }
}