<?php
namespace CsvCombine\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component;
use Cake\Controller\Controller;

use CsvCombine\Controller\Component\FixedLengthExportComponent;
use Cake\TestSuite\TestCase;

/**
 * CsvCombine\Test\TestCase\Controller\Component\FixedLengthExportComponent Test Case
 */
class FixedLengthExportComponentTest extends TestCase
{

    public $components = ['CsvCombine.FixedLengthExport'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Controller = new Controller();
        $this->ComponentRegistry = new ComponentRegistry($this->Controller);
        $this->FixedLengthExport = new FixedLengthExportComponent($this->ComponentRegistry);

        $this->test1_fixed_length_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/test_app/test1.txt';
        $this->test2_fixed_length_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/test_app/test2.txt';
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->FixedLengthExport);

        parent::tearDown();

        //不要なファイルを削除する
        unlink($this->test2_fixed_length_path);
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function test_make()
    {
        $test2_fixed_length_path_pathinfo = pathinfo($this->test2_fixed_length_path);
        //CSV1と同じ内容を作成
        $list = [
            [
                'あいう',
                'いいい',
                'uuu',
            ],
            [
                'いうえ',
                'ううう',
                'eee',
            ],
            [
                'abcde',
                'fggf',
                'おお',
            ],
        ];
        $fixed_options = [
            8,
            10,
            6
        ];

        $this->FixedLengthExport->make($list, $fixed_options, $test2_fixed_length_path_pathinfo['basename'], "\r\n",$test2_fixed_length_path_pathinfo['dirname'] . '/');

        $fixed_length1_fp = fopen($this->test1_fixed_length_path ,'r');
        $fixed_length1 = fread($fixed_length1_fp, filesize($this->test1_fixed_length_path));
        fclose($fixed_length1_fp);

        $fixed_length2_fp = fopen($this->test2_fixed_length_path ,'r');
        $fixed_length2 = fread($fixed_length2_fp, filesize($this->test2_fixed_length_path));
        fclose($fixed_length2_fp);

        //同じ内容で作成ができているかを確認
        $this->assertEquals($fixed_length1, $fixed_length2);

    }

}
