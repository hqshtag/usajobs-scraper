<?php

/**
 * Helper functions.
 *
 * @link       https://wajihtagourty.ml/
 * @since      1.0.0
 *
 * @package    Usajobs_Scraper
 * @subpackage Usajobs_Scraper/admin
 * @author     Wajih <Wajih.tagourty@gmail.com>
 */
class Usajobs_Scraper_helpers
{
    /**
     * parses date
     * @param String YYYY-MM-DD 
     * @return String  DDth of Monthname YYYY
     */

    public function parse_date(string $date)
    {
        $date_array = preg_split("/[-]+/", $date);
        $year = $date_array[0];
        $month = date("F", mktime(0, 0, 0, $date_array[1], 10));
        $day = $date_array[2];
        $day = $this->ordinal($day);
        return "$day of $month $year";
    }

    /**
     * 
     * @param String any natural number
     * @return String number(th/st/nd/rd)
     */
    public function ordinal($day)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        $number = (int)$day;
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return $day . 'th';
        else
            return "$day" . $ends[$number  % 10];
    }

    public function parse_money(String $money)
    {
        $money = (float)$money;
        $money = (string)$money;
        $money_arr = preg_split('/[.]/', $money);
        if (count($money_arr) == 2) {
            $under0 = $money_arr[1];
        } else {
            $under0 = 0;
        }
        $over0 = $this->parse_num($money_arr[0]);


        return $under0 == 0 ? "$$over0" : "$over0.$under0";
    }

    public function parse_num($num)
    {
        if ($num >= 1000) {
            $k = $num / 1000;
            $k = preg_split('/[.]/', $k);
            $k = $k[0];
            $rest = $num % 1000;
            if ($rest < 10) {
                return "$k,00$rest";
            } else if ($rest < 100) {
                return "$k,0$rest";
            }

            return "$k,$rest";
        } else {
            return "$num";
        }
    }
}
