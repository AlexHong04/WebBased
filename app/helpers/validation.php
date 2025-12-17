<?php

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}


function is_money($value)
{
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function is_date($value, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) == $value;
}

function is_time($value, $format = 'H:i')
{
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) == $value;
}

function get_years($min, $max, $reverse = false)
{
    $arr = range($min, $max);
    if ($reverse) $arr = array_reverse($arr);
    return array_combine($arr, $arr);
}

function get_months()
{
    return [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    ];
}

function root($path = '')
{
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}
function base($path = '') {
    return "http://$_SERVER[HTTP_HOST]/$path";
}


function array_all($arr, $fn)
{
    foreach ($arr as $k => $v) if (!$fn($v, $k)) return false;
    return true;
}


