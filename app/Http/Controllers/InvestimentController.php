<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvestimentController extends Controller
{
    /**
     * Get the gain rate
     * @return float
     */
    private function getGainRate()
    {
        $gain_rate = 0.0052; // 0,52%
        return $gain_rate;
    }


    /**
     * Get the tax rate according to time invested
     * @param  int  $age
     * @return float
     */
    private function getTaxRate($age)
    {
        $tax = 0;

        // If it is less than one year old, the percentage will be 22.5%
        if ($age < 12) {
            $tax = 22.5;
        }

        // If it is between one and two years old, the percentage will be 18.5%
        elseif ($age <= 24) {
            $tax = 18.5;
        }

        // If older than two years, the percentage will be 15%
        else {
            $tax = 15;
        }

        $tax /= 100;

        return $tax;
    }
}
