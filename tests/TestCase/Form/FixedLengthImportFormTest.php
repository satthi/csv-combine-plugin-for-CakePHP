<?php
namespace CsvCombine\Test\TestCase\Form;

use CsvCombine\Form\FixedLengthImportForm;
use Cake\TestSuite\TestCase;

/**
 * CsvCombine\Form\FixedLengthImportForm Test Case
 */
class FixedLengthImportFormTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Form = new FixedLengthImportForm();
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
        $test1_fixed_length_path = dirname(dirname(dirname(__FILE__))) . '/test_app/test1.txt';
        $column_list = [
            ['name' => 'column1', 'length' => 8],
            ['name' => 'column2', 'length' => 10],
            ['name' => 'column3', 'length' => 6],
        ];
        $fixedLengthData = $this->Form->loadData($test1_fixed_length_path, $column_list);
        //テストファイル
        //1行目
        $result1 = [
            'column1' => 'あいう',
            'column2' => 'いいい',
            'column3' => 'uuu'
        ];
        $this->assertTrue(
            $fixedLengthData[0] === $result1
        );

        //2行目
        $result2 = [
            'column1' => 'いうえ',
            'column2' => 'ううう',
            'column3' => 'eee'
        ];
        $this->assertTrue(
            $fixedLengthData[1] === $result2
        );

        //3行目
        $result3 = [
            'column1' => 'abcde',
            'column2' => 'fggf',
            'column3' => 'おお'
        ];
        $this->assertTrue(
            $fixedLengthData[2] === $result3
        );
    }


}
