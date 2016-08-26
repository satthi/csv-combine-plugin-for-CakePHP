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
                'uu',
                'u',
            ],
            [
                'いうえ',
                'ううう',
                'eee',
            ],
            [
                'ab',
                'cde',
                'fggf',
                'おお',
            ],
        ];
        $fixed_options = [
            ['length' => 8, 'type' => 'text'],
            ['length' => 10, 'type' => 'text'],
            ['length' => 6, 'type' => 'text'],
        ];
        $header_options = [
            ['length' => 8, 'type' => 'text'],
            ['length' => 10, 'type' => 'text'],
            ['length' => 2, 'type' => 'text'],
            ['length' => 4, 'type' => 'text'],
        ];
        $footer_options = [
            ['length' => 2, 'type' => 'text'],
            ['length' => 6, 'type' => 'text'],
            ['length' => 10, 'type' => 'text'],
            ['length' => 6, 'type' => 'text'],
        ];
        $options = [
            'file_name' => $test2_fixed_length_path_pathinfo['basename'],
            'directory' => $test2_fixed_length_path_pathinfo['dirname'] . '/',
            'extra_fixed_options' => [
                1 => $header_options,
                -1 => $footer_options,
            ]
        ];
        $this->FixedLengthExport->make($list, $fixed_options, $options);

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
