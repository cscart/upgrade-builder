<?php

/**
 * Class AddonBuilderTest
 */
class AddonBuilderTest extends PHPUnit_Framework_TestCase
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

    public function testUpgradeFiles()
    {
        $release1 = new \upgrade\builder\ReleaseArchive(__DIR__ . '/data/releases/addon_v1.0.0.tgz', 'sample', '1.0.0');
        $release2 = new \upgrade\builder\ReleaseArchive(__DIR__ . '/data/releases/addon_v2.0.0.tgz', 'sample', '2.0.0');

        $builder = new \upgrade\builder\AddonBuilder($release1, $release2, __DIR__ . '/data/runtime/tmp');
        $builder->initPaths();

        $builder->unpackArchives();

        $this->assertEquals(
            array(
                __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/migrations/62312332434_test_migration1.php',
                __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/migrations/62312332434_test_migration2.php'
            ),
            $builder->getMigrations()
        );

        $this->assertEquals(
            array(
                __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/validators/validator1.php',
                __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/validators/validator2.php'
            ),
            $builder->getValidators()
        );

        $this->assertEquals(
            array(
                __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/extra_files/test.php',
            ),
            $builder->getExtraFiles()
        );

        $this->assertEquals(
            array(
                'extra' => 1
            ),
            $builder->getExtraPackageData()
        );

        $this->assertEquals(
            __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/scripts/pre_script.php',
            $builder->getPreScriptFile()
        );

        $this->assertEquals(
            __DIR__ . '/data/runtime/tmp/to/app/addons/sample/upgrades/2.0.0/scripts/post_script.php',
            $builder->getPostScriptFile()
        );
    }
}