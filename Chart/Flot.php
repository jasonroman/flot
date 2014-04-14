<?php

namespace JasonRoman\Flot\Chart;

/**
 * Class for handling data between PHP and the Javascript Flot library
 * 
 * @author Jason Roman <j@jayroman.com>
 */
class Flot
{
    /**
     * Convert an array of normal PHP data into a format that Flot can understand
     * 
     *  - works for standard flot charts, both vertical and horizontal
     *  - works with the time plugin and allows passing in datetime strings or unix timestamps as the keys
     *  - does not work with pie charts - @see the convertToPie() function for this
     * 
     * Expects an array of key/value pairs (single series), or an array of arrays of key/value pairs
     * 
     * ex:
     *  - array('Item1' => 5, 'Item2' => 10)
     *  - array(array('Series1Item1' => 5, 'Series1Item2' => 10), array('Series2Item1' => 15, 'Series2Item2' => 20))
     *  - array('2014-01-01 12:00:00' => 5, '2014-06-01 12:00:00' => 10)
     *  - array(array('2014-01-01' => 5, '2014-06-01' => 10), array('2014-01-01' => 15, '2014-06-01' => 20))
     *  - array(1388574000 => 5, 1401616800 => 10)
     *  - array(array(1388574000 => 5, 1401616800 => 10), array(1388574000 => 15, 1401616800 => 20))
     * 
     * @param array $data the data to convert
     * @param string $orientation
     * @param boolean $datetime
     * @return json array of chart series data
     */
    public function convert(array $data, $orientation = 'vertical', $datetime = false)
    {
        $chartData = array();

        // if only one series passed in (single-dimensional array), wrap in array for the series looping
        if (!$this->hasMultipleSeries($data)) {
            $data = array($data);
        }

        // loop through each series and convert its data
        foreach ($data as $series)
        {
            $seriesData = array();

            // convert each key/value pair in the series
            foreach ($series as $key => $value)
            {
                // if a date-time chart, convert keys that are either a datetime string or unix timestamp
                if ($datetime)
                {
                    // convert to unix timestamp if passed in a string datetime
                    $unixTimestamp = (is_string($key)) ? strtotime($key) : $key;

                    // flot takes data in milliseconds, so multiply the timestamp by 1000
                    $key = $unixTimestamp * 1000;
                }

                // pass in as (x, y) or (y, x) coordinates depending on of vertical or horizontal orientation
                $seriesData[] = ($orientation == 'vertical') ? array($key, $value) : array($value, $key);
            }

            $chartData[] = array('data' => $seriesData);
        }

        return json_encode($chartData);
    }

    /**
     * Converts pie chart data to the pie chart Flot data format; expects a single array of key/value pairs
     * 
     * @param array $data the data to convert
     * @return json array of series data with labels and data; empty if no data or trying to convert multiple series
     */
    public function convertToPie(array $data)
    {
        // make sure not trying to convert data that contains multiple series
        if ($this->hasMultipleSeries($data)) {
            return json_encode(array());
        }

        $chartData = array();

        foreach ($data as $label => $data) {
            $chartData[] = array('label' => $label, 'data' => $data);
        }

        return json_encode($chartData);
    }

    /**
     * Returns if an array has multiple series; use array_filter() to check is_array()
     * since count() may return 0 or fewer values if data in the array is empty
     * 
     * return true:    array(array(...), array(...), array(...))
     * returns false:  array(...)
     * 
     * @param array $array
     * @return boolean
     */
    public function hasMultipleSeries(array $array)
    {
        return (count(array_filter($array, 'is_array')) > 0);
    }
}