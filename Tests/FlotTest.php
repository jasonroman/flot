<?php

namespace JasonRoman\Flot\Tests;

use JasonRoman\Flot\Flot;

/**
 * Flot Chart unit tests
 * 
 * @author Jason Roman <j@jayroman.com>
 */
class FlotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JasonRoman\Flot\Flot
     */
    private static $class;


    /**
     * Runs once before entire suite of tests
     */
    public static function setUpBeforeClass()
    {
        self::$class = new Flot();
    }

    /**
     * Runs once after entire suite of tests
     */
    public static function tearDownAfterClass()
    {
        // apparently array_filter($array, is_array()); breaks code coverage generation for the following line
        //self::$class = null;
    }

    /**
     * Tests converting an array of keys/values and orientation to Flot chart data format
     * 
     * @dataProvider chartDataProvider
     */
    public function testConvert(array $data, $orientation)
    {
        $converted = self::$class->convert($data, $orientation);

        $this->assertTrue(self::isJson($converted));

        // rebuild the decoded array back to the original format to make sure it matches
        $rebuiltData = self::rebuildChartData(json_decode($converted), $orientation);

        $this->assertSame($data, $rebuiltData);
    }

    /**
     * Tests converting an array of unix timestamp keys/values and orientation to Flot time chart data format
     * 
     * @dataProvider chartDatetimeDataProvider
     */
    public function testConvertDatetime(array $data, $orientation)
    {
        $converted = self::$class->convert($data, $orientation, true);

        $this->assertTrue(self::isJson($converted));

        // rebuild the decoded array back to the original format to make sure it matches
        $rebuiltData = self::rebuildChartData(json_decode($converted), $orientation, true);

        $this->assertSame($data, $rebuiltData);
    }


    /**
     * Tests converting an array of datetime string keys/values and orientation to Flot time chart data format
     * 
     * @dataProvider chartDatetimeStringDataProvider
     */
    public function testConvertDatetimeString(array $data, $orientation)
    {
        $converted = self::$class->convert($data, $orientation, true);

        $this->assertTrue(self::isJson($converted));

        // rebuild the decoded array back to the original format to make sure it matches
        $rebuiltData = self::rebuildChartData(json_decode($converted), $orientation, true, true);

        $this->assertSame($data, $rebuiltData);
    }

    /**
     * Tests converting an array of keys/values to Flot pie chart format
     * 
     * @dataProvider singleSeriesProvider
     */
    public function testConvertToPieValid(array $data)
    {
        $converted = self::$class->convertToPie($data);

        $this->assertTrue(self::isJson($converted));

        // now make sure the keys and values were set properly as label/data object members
        // if the initial array had no keys, then keys will just be the index
        $decoded = json_decode($converted);

        $i = 0;

        // array of arrays are returned for the decoded objects, so match with each data
        foreach ($data as $key => $value)
        {
            $this->assertSame($key, $decoded[$i]->label);
            $this->assertSame($value, $decoded[$i]->data);

            $i++;
        }
    }

    /**
     * Tests failure upon trying to convert data with multiple series
     * 
     * @dataProvider multipleSeriesProvider
     */
    public function testConvertToPieInvalid(array $data)
    {
        $this->assertSame(json_encode(array()), self::$class->convertToPie($data));
    }

    /**
     * Tests that the data has multiple series
     * 
     * @dataProvider multipleSeriesProvider
     */
    public function testHasMultipleSeriesTrue(array $data)
    {
        $this->assertTrue(self::$class->hasMultipleSeries($data));
    }

    /**
     * Tests that the data is a single series
     * 
     * @dataProvider singleSeriesProvider
     */
    public function testHasMultipleSeriesFalse(array $data)
    {
        $this->assertFalse(self::$class->hasMultipleSeries($data));
    }

    /**
     * Checks if data is a json-encoded object
     * 
     * @param mixed $data
     * @return bool
     */
    public static function isJson($data)
    {
        return (is_array(json_decode($data, true)));
    }

    /**
     * Take chart data that was converted to Flot format and convert back to a normal array;
     * Used for comparing converted data back to the original to make sure it is the same
     * 
     * @param array $data
     * @return array
     */
    public function rebuildChartData(array $data, $orientation = 'vertical', $datetime = false, $datetimeString = false)
    {
        $i = 0;

        $chartData = array();

        // rebuild the decoded array to make sure it matches to the original
        foreach ($data as $series)
        {
            $seriesData = array();

            foreach ($series->data as $point)
            {
                // set as (x => y) or (y => x) depending on graph orientation and account for time
                if ($orientation == 'vertical')
                {
                    $key    = $point[0];
                    $value  = $point[1];
                }
                else
                {
                    $key    = $point[1];
                    $value  = $point[0];
                }

                if ($datetime)
                {
                    // convert to unix timestamp if passed in a string datetime
                    if ($datetimeString) {
                        $key = date('Y-m-d H:i:s', ($key / 1000));
                    }
                    // flot takes data in milliseconds, so divide the timestamp by 1000
                    else {
                        $key = $key / 1000;
                    }
                }

                $seriesData[$key] = $value;
            }

            $chartData[] = $seriesData;

            $i++;
        }

        // if a single series was provided, convert back to a single series
        if (count($data) == 1) {
            $chartData = $chartData[0];
        }

        return $chartData;
    }

    /**
     * Returns arrays of chart data and specified orientation capable of being converted to Flot data format
     */
    public function chartDataProvider()
    {
        return array(
            array('data' => array('test'), 'orientation' => 'vertical'),
            array('data' => array('key1' => 'value1', 'key2' => 'value2'), 'orientation' => 'horizontal'),
            array('data' => array(), 'orientation' => 'vertical'),
            array('data' => array(1, 2, 3, 4, 5), 'orientation' => 'horizontal'),
            array(
                'data' => array(
                    array('key1' => 'value1', 'key2' => 'value2'),
                    array('key3' => 'value3', 'key4' => 'value4')
                ),
                'orientation' => 'vertical'
            ),
            array('data' => array(array(1, 2, 3), array(4, 5, 6)), 'orientation' => 'horizontal'),
            array('data' => array(array('test'), array('anothertest')), 'orientation' => 'vertical'),
            array('data' => array(array('test'), array()), 'orientation' => 'horizontal'),
        );
    } 

    /**
     * Returns arrays of unix timestamp chart data and orientation capable of being converted to Flot data format
     */
    public function chartDatetimeDataProvider()
    {
        return array(
            array('data' => array(1357027200 => 10, 1357113600 => 5), 'orientation' => 'vertical'),
            array('data' => array(1357027200 => 10, 1357113600 => 5), 'orientation' => 'horizontal'),
            array(
                'data' => array(
                    array(1357027200 => 10, 1357113600 => 5),
                    array(1357027200 => 20, 1357113600 => 30),
                ),
                'orientation' => 'vertical'
            ),
            array(
                'data' => array(
                    array(1357027200 => 10, 1357113600 => 5),
                    array(1357027200 => 20, 1357113600 => 30),
                ),
                'orientation' => 'horizontal'
            ),
        );
    }

    /**
     * Returns arrays of datetime string data and orientation capable of being converted to Flot data format
     */
    public function chartDatetimeStringDataProvider()
    {
        return array(
            array('data' => array('2013-01-01 00:00:00' => 10, '2013-02-01 00:00:00' => 5), 'orientation' => 'vertical'),
            array('data' => array('2013-01-01 00:00:00' => 10, '2013-02-01 00:00:00' => 5), 'orientation' => 'horizontal'),
            array(
                'data' => array(
                    array('2013-01-01 00:00:00' => 10, '2013-02-01 00:00:00' => 5),
                    array('2013-01-01 00:00:00' => 20, '2013-02-01 00:00:00' => 30),
                ),
                'orientation' => 'vertical'
            ),
            array(
                'data' => array(
                    array('2013-01-01 00:00:00' => 10, '2013-02-01 00:00:00' => 5),
                    array('2013-01-01 00:00:00' => 20, '2013-02-01 00:00:00' => 30),
                ),
                'orientation' => 'horizontal'
            ),
        );
    }

    /**
     * Returns arrays that have multiple series
     */
    public function multipleSeriesProvider()
    {
        return array(
            array(array(array('key1' => 'value1', 'key2' => 'value2'), array('key3' => 'value3', 'key4' => 'value4'))),
            array(array(array(1, 2, 3, 9 => 10), array(4, 5, 6))),
            array(array(array('test'), array('anothertest'))),
            array(array(array('test'), array())),
        );
    }

    /**
     * Returns arrays that have one series
     */
    public function singleSeriesProvider()
    {
        return array(
            array(array('test')),
            array(array('key1' => 'value1', 'key2' => 'value2')),
            array(array()),
            array(array(1, 2, 3, 4, 5)),
        );
    }
}