<?php

namespace index\controllers;

class Analyze
{
    public function index()
    {
        $data = require __DIR__.'/../data.php';

        $closePrices = array_column($data, 'close');

        $lowerPoint = new \stdClass;
        $higherPoint = new \stdClass;

        if ($closePrices[0] < $closePrices[1]) {
            $lower = $closePrices[0];
            $higher = $closePrices[1];
            $trend = 'up';
            if ($higher/$lower - 1 > 0.06) {
                $higherPoint->price = $higher;
                $higherPoint->time = 1;
            }
        } else {
            $lower = $closePrices[1];
            $higher = $closePrices[0];
            if (1 - $lower/$higher > 0.06) {
                $lowerPoint->price = $lower;
                $lowerPoint->time = 0;
            }
            $trend = 'down';
        }

        for ($i=2; $i < count($closePrices); $i++) { 
            if ($closePrices[$i] > $higher) {
                $higher = $closePrices[$i];
                if ($higher/$lower - 1 > 0.06) {
                    $higherPoint->price = $higher;
                    $higherPoint->time = $i;
                }
            } elseif ($closePrices[$i] < $lower) {
                $lower = $closePrices[$i];
                if (1 - $lower/$higher > 0.06) {
                    $lowerPoint->price = $lower;
                    $lowerPoint->time = $i;
                }
            } elseif (isset($lowerPoint)) {
                if (isset($higherPoint)) {
                    if ($closePrices[$i] > $higherPoint) {
                        $higherPoint = $closePrices[$i];
                    }
                } elseif ($closePrices[$i]/$lowerPoint - 1 > 0.06) {
                    $higherPoint = $closePrices[$i];
                }
            }
        }
    }
}
