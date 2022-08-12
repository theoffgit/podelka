<?php

function q($sql, $params)
{
    return AlefSQL::q($sql, $params);
}

function q1($sql, $params)
{
    return AlefSQL::q1($sql, $params);
}

function qi($sql, $params)
{
    return AlefSQL::qi($sql, $params);
}

function dq($sql, $params)
{
    return AlefSQL::dq($sql, $params);
}

function qInsertId()
{
    return AlefSQL::qInsertId();
}

function flog($txt)
{
    AlefLog::info($txt);
}

function _localize($string)
{
    return AlefLocalizer::_localize($string);
}
