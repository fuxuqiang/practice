<?php

function changeRate($curr, $base): ?float
{
    return $base ? ($curr - $base) / $base : null;
}