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
        $extra_list = [
            //ヘッダー
            1 => [
                ['name' => 'columna', 'length' => 4],
                ['name' => 'columnb', 'length' => 8],
                ['name' => 'columnc', 'length' => 12],
            ],
            //フッター
            -1 => [
                ['name' => 'columnx', 'length' => 2],
                ['name' => 'columny', 'length' => 12],
                ['name' => 'columnz', 'length' => 10],
            ]
        ];
        $options = ['extra_fixed_options' => $extra_list];
        $fixedLengthData = $this->Form->loadData($test1_fixed_length_path, $column_list, $options);
        //テストファイル
        //1行目
        $result1 = [
            'columna' => 'あい',
            'columnb' => 'う  いい',
            'columnc' => 'い    uuu'
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
            'columnx' => 'ab',
            'columny' => 'cde   fggf',
            'columnz' => '    おお'
        ];
        $this->assertTrue(
            $fixedLengthData[2] === $result3
        );
    }


}
