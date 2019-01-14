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

    /**
     * @dataProvider packageProvider
     */
    public function testUpgradeFiles($package1, $package2)
    {
        $release1 = new \upgrade\builder\ReleaseArchive($package1['path'], $package1['name'], $package1['version']);
        $release2 = new \upgrade\builder\ReleaseArchive($package2['path'], $package2['name'], $package2['version']);

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

    public function packageProvider()
    {
        return array(
            array(
                array(
                    'path' => __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                    'name' => 'sample',
                    'version' => '1.0.0'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/addon_v2.0.0.tgz',
                    'name' => 'sample',
                    'version' => '2.0.0'
                ),
            ),
            array(
                array(
                    'path' => __DIR__ . '/data/releases/addon_v1.0.0.zip',
                    'name' => 'sample',
                    'version' => '1.0.0'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/addon_v2.0.0.zip',
                    'name' => 'sample',
                    'version' => '2.0.0'
                ),
            ),
            array(
                array(
                    'path' => __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                    'name' => 'sample',
                    'version' => '1.0.0'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/addon_v2.0.0.zip',
                    'name' => 'sample',
                    'version' => '2.0.0'
                ),
            ),
            array(
                array(
                    'path' => __DIR__ . '/data/releases/addon_v1.0.0.zip',
                    'name' => 'sample',
                    'version' => '1.0.0'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/addon_v2.0.0.tgz',
                    'name' => 'sample',
                    'version' => '2.0.0'
                ),
            ),
        );
    }
}