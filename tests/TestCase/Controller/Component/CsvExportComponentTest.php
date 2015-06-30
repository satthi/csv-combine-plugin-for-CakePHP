<?php
namespace CsvCombine\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component;
use Cake\Controller\Controller;

use CsvCombine\Controller\Component\CsvExportComponent;
use Cake\TestSuite\TestCase;

/**
 * CsvCombine\Test\TestCase\Controller\Component\CsvExportComponent Test Case
 */
class CsvExportComponentTest extends TestCase
{

    public $components = ['CsvCombine.CsvExport'];
    
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
        $this->CsvExport = new CsvExportComponent($this->ComponentRegistry);
        
        $this->test1_csv_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/test_app/test1.csv';
        $this->test2_csv_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/test_app/test2.csv';
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CsvExport);

        parent::tearDown();
        
        //不要なファイルを削除する
        unlink($this->test2_csv_path);
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function test_make()
    {
        $test2_csv_path_pathinfo = pathinfo($this->test2_csv_path);
        //CSV1と同じ内容を作成
        $lists = [
            [
                '1',
                '2',
                '3',
            ],
            [
                'あ',
                'い',
                'う',
            ],
            [
                '"hoge',
                "\r\n",
                '',
            ],
        ];
        
        
        $this->CsvExport->make($lists, $test2_csv_path_pathinfo['basename'], ',', $test2_csv_path_pathinfo['dirname'] . '/');
        
        $csv1_fp = fopen($this->test1_csv_path ,'r');
        $csv1 = fread($csv1_fp, filesize($this->test1_csv_path));
        fclose($csv1_fp);
        
        $csv2_fp = fopen($this->test2_csv_path ,'r');
        $csv2 = fread($csv2_fp, filesize($this->test2_csv_path));
        fclose($csv2_fp);
        
        //同じ内容で作成ができているかを確認
        $this->assertEquals($csv1, $csv2);
        
        
    }
    

}
