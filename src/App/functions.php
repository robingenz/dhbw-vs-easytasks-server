<?php
if (!function_exists('evalBool')) {
    function evalBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
