<?php
class Alerts
{
    public static function primary($Value, $Class = '', $Additional = '') { return static::print('primary', $Value, $Class, $Additional); }
    public static function secondary($Value, $Class = '', $Additional = '') { return static::print('secondary', $Value, $Class, $Additional); }
    public static function success($Value, $Class = '', $Additional = '') { return static::print('success', $Value, $Class, $Additional); }
    public static function danger($Value, $Class = '', $Additional = '') { return static::print('danger', $Value, $Class, $Additional); }
    public static function warning($Value, $Class = '', $Additional = '') { return static::print('warning', $Value, $Class, $Additional); }
    public static function info($Value, $Class = '', $Additional = '') { return static::print('info', $Value, $Class, $Additional); }
    public static function light($Value, $Class = '', $Additional = '') { return static::print('light', $Value, $Class, $Additional); }
    public static function dark($Value, $Class = '', $Additional = '') { return static::print('dark', $Value, $Class, $Additional); }

    private static function print($Type, $Value, $Class = '', $Additional = '')
    {
        return '<div class="alert alert-' . $Type . ($Class ? ' ' . $Class : '') . '"' . ($Additional ? ' ' . $Additional : '') . '>' . $Value . '</div>';
    }
}