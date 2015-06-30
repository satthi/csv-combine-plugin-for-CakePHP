<?php
namespace CsvCombine\Test\TestCase\Form;

use CsvCombine\Form\CsvImportForm;
use Cake\TestSuite\TestCase;

/**
 * CsvCombine\Form\CsvImportForm Test Case
 */
class CsvImportFormTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Form = new CsvImportForm();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Form);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function test_loadCsv()
    {
        $test1_csv_path = dirname(dirname(dirname(__FILE__))) . '/test_app/test1.csv';
        $column = [
            'column1',
            'column2',
            'column3',
        ];
        $csvData = $this->Form->loadDataCsv($test1_csv_path, $column);
        //テストファイル
        //1行目
        $result1 = [
            'column1' => '1',
            'column2' => '2',
            'column3' => '3'
        ];
        $this->assertTrue(
            $csvData[0] === $result1
        );
        
        //2行目
        $result2 = [
            'column1' => 'あ',
            'column2' => 'い',
            'column3' => 'う'
        ];
        $this->assertTrue(
            $csvData[1] === $result2
        );
        
        //3行目
        $result3 = [
            'column1' => '"hoge',
            'column2' => "\r\n",
            'column3' => ''
        ];
        $this->assertTrue(
            $csvData[2] === $result3
        );
    }
    

}
