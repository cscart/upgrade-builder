<?php

/**
 * Class CoreBuilderTest
 */
class CoreBuilderTest extends PHPUnit_Framework_TestCase
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

        $builder = new \upgrade\builder\CoreBuilder($release1, $release2, __DIR__ . '/data/runtime/tmp');

        $builder->setMigrationsPath(__DIR__ . '/data/upgrade/migrations');
        $builder->setValidatorsPath(__DIR__ . '/data/upgrade/validators');
        $builder->setScriptsPath(__DIR__ . '/data/upgrade/scripts');
        $builder->setExtraFilesPath(__DIR__ . '/data/upgrade/extra_files');
        $builder->setExtraPath(__DIR__ . '/data/upgrade/extra/extra.php');

        $this->assertEquals(
            array(
                __DIR__ . '/data/upgrade/migrations/62312332434_test_migration1.php',
                __DIR__ . '/data/upgrade/migrations/62312332434_test_migration2.php'
            ),
            $builder->getMigrations()
        );

        $builder->unpackArchives();
        $files = $builder->getFiles();

        $this->assertEquals('deleted', $files['app/addons/ebay/addon.xml']['status']);
        $this->assertEquals('new', $files['app/addons/sample/addon.xml']['status']);
        $this->assertEquals('new', $files['app/addons/twigmo/addon.xml']['status']);
        $this->assertEquals('new', $files['app/addons/twigmo/file1.php']['status']);
        $this->assertEquals('new', $files['app/addons/twigmo_best/addon.xml']['status']);
        $this->assertEquals('new', $files['app/addons/twigmo_best/file1.php']['status']);

        $builder->setExcludeAddons(array(
            'ebay',
            'sample',
            'twigmo' => array(
                'file1.php'
            )
        ));

        $files = $builder->getFiles();

        $this->assertArrayNotHasKey('app/addons/ebay/addon.xml', $files);
        $this->assertArrayNotHasKey('app/addons/ebay/addon.xml', $files);
        $this->assertArrayNotHasKey('app/addons/twigmo/addon.xml', $files);
        $this->assertArrayNotHasKey('js/addons/twigmo/test.txt', $files);
        $this->assertArrayNotHasKey('var/langs/en/addons/twigmo.po', $files);
        $this->assertArrayHasKey('app/addons/twigmo_best/addon.xml', $files);
        $this->assertArrayHasKey('app/addons/twigmo_best/file1.php', $files);
        $this->assertArrayHasKey('js/addons/twigmo_best/test.txt', $files);
        $this->assertArrayHasKey('var/langs/en/addons/twigmo_best.po', $files);
        $this->assertEquals('new', $files['app/addons/twigmo/file1.php']['status']);
        $this->assertEquals('new', $files['app/addons/twigmo_best/addon.xml']['status']);
        $this->assertEquals('new', $files['app/addons/twigmo_best/file1.php']['status']);
        $this->assertEquals('new', $files['sample.php']['status']);

        $builder->setExcludeFiles(array('app/addons'));
        $files = $builder->getFiles();

        $this->assertArrayNotHasKey('app/addons/ebay/addon.xml', $files);
        $this->assertArrayNotHasKey('app/addons/ebay/addon.xml', $files);
        $this->assertArrayNotHasKey('app/addons/twigmo/addon.xml', $files);
        $this->assertArrayNotHasKey('app/addons/twigmo/file1.php', $files);
        $this->assertArrayNotHasKey('app/addons/sample/addon.xml', $files);
        $this->assertArrayHasKey('js/addons/twigmo_best/test.txt', $files);
    }

    public function packageProvider()
    {
        return array(
            array(
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.1.tgz',
                    'name' => 'ultimate',
                    'version' => '4.3.1'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.2.tgz',
                    'name' => 'ultimate',
                    'version' => '4.3.2'
                )
            ),
            array(
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.1.zip',
                    'name' => 'ultimate',
                    'version' => '4.3.1'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.2.zip',
                    'name' => 'ultimate',
                    'version' => '4.3.2'
                )
            ),
            array(
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.1.tgz',
                    'name' => 'ultimate',
                    'version' => '4.3.1'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.2.zip',
                    'name' => 'ultimate',
                    'version' => '4.3.2'
                )
            ),
            array(
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.1.zip',
                    'name' => 'ultimate',
                    'version' => '4.3.1'
                ),
                array(
                    'path' => __DIR__ . '/data/releases/ultimate_4.3.2.tgz',
                    'name' => 'ultimate',
                    'version' => '4.3.2'
                )
            )
        );
    }
}