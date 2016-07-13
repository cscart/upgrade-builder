<?php

/**
 * Class TarArchiverTest
 */
class TarArchiverTest extends PHPUnit_Framework_TestCase
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
     * @dataProvider findProvider
     */
    public function testFind($archive, $regex, $expected_files)
    {
        $archiver = new \upgrade\builder\TarArchiver();
        $found_files = $archiver->find($archive, $regex);

        $this->assertEquals($expected_files, $found_files);
    }

    /**
     * @dataProvider extractProvider
     */
    public function testExtract($archive, $files)
    {
        $tmp_dir = __DIR__ . '/data/runtime/tmp';

        $archiver = new \upgrade\builder\TarArchiver();
        $archiver->extract($archive, $tmp_dir, $files);

        $files_exist = true;
        foreach ($files as $file) {
            $files_exist = $files_exist && is_file($tmp_dir . '/' . $file);
        }

        $this->assertTrue($files_exist);
    }

    public function findProvider()
    {
        return array(
            array(
                __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                'app/addons/.*/addon.xml',
                array('app/addons/sample/addon.xml')
            ),
            array(
                __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                'app/addons/.*/.*.php',
                array('app/addons/sample/func.php', 'app/addons/sample/init.php')
            ),
            array(
                __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                'app/addons/.*/missing.file',
                array()
            ),
        );
    }

    public function extractProvider()
    {
        return array(
            array(
                __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                array('app/addons/sample/addon.xml')
            ),
            array(
                __DIR__ . '/data/releases/addon_v1.0.0.tgz',
                array('app/addons/sample/func.php', 'app/addons/sample/init.php')
            ),
        );
    }
}