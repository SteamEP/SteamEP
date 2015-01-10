<?php

class Shared {

    public static function getColumn($objects, $columnname)
    {
		$return = array();
		foreach ($objects as $object)
		{
			$return[] = $object->{$columnname};
		}
		return $return;
    }

    public static function toKeyedArray($objects, $key)
    {
        $array = array();
        foreach ($objects as $object)
        {
            $array[$object->{$key}] = $object;
        }
        return $array;
    }

    public static function timespan($seconds = 1, $time = '')
    {
        if (!is_numeric($seconds))
        {
            $seconds = 1;
        }

        if (!is_numeric($time))
        {
            $time = time();
        }

        if ($time <= $seconds)
        {
            $seconds = 1;
        }
        else
        {
            $seconds = $time - $seconds;
        }

        $str   = '';
        $years = floor($seconds / 31536000);

        if ($years > 0)
        {
            $str .= $years . ' ' . (($years > 1) ? 'Years' : 'Year') . ', ';
        }

        $seconds -= $years * 31536000;
        $months = floor($seconds / 2628000);

        if ($years > 0 OR $months > 0)
        {
            if ($months > 0)
            {
                $str .= $months . ' ' . (($months > 1) ? 'Months' : 'Month') . ', ';
            }

            $seconds -= $months * 2628000;
        }

        $weeks = floor($seconds / 604800);

        if ($years > 0 OR $months > 0 OR $weeks > 0)
        {
            if ($weeks > 0)
            {
                $str .= $weeks . ' ' . (($weeks > 1) ? 'Weeks' : 'Week') . ', ';
            }

            $seconds -= $weeks * 604800;
        }

        $days = floor($seconds / 86400);

        if ($months > 0 OR $weeks > 0 OR $days > 0)
        {
            if ($days > 0)
            {
                $str .= $days . ' ' . (($days > 1) ? 'Days' : 'Day') . ', ';
            }

            $seconds -= $days * 86400;
        }

        $hours = floor($seconds / 3600);

        if ($days > 0 OR $hours > 0)
        {
            if ($hours > 0)
            {
                $str .= $hours . ' ' . (($hours > 1) ? 'Hours' : 'Hour') . ', ';
            }

            $seconds -= $hours * 3600;
        }

        $minutes = floor($seconds / 60);

        if ($days > 0 OR $hours > 0 OR $minutes > 0)
        {
            if ($minutes > 0)
            {
                $str .= $minutes . ' ' . (($minutes > 1) ? 'Minutes' : 'Minute') . ', ';
            }

            $seconds -= $minutes * 60;
        }

        if ($str == '')
        {
            $str .= $seconds . ' ' . (($seconds > 1) ? 'Seconds' : 'Second') . ', ';
        }

        return substr(trim($str), 0, -1);
    }

}

