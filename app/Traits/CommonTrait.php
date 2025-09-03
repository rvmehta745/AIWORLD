<?php

namespace App\Traits;

trait CommonTrait
{
    /**
     * Clean string or array.
     *
     * @param string|array
     * @return string|array
     */
    public function cleanString($badString)
    {
        if (is_array($badString)) {
            foreach ($badString as &$badStringO) {
                $badStringO = filter_var($badStringO, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
                $badStringO = trim(strip_tags(mb_convert_encoding(utf8_encode($badStringO), 'UTF-8', 'UTF-8')));
            }
        } elseif (!empty($badString)) {
            $badString = filter_var($badString, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
            $badString = trim(strip_tags(mb_convert_encoding(utf8_encode($badString), 'UTF-8', 'UTF-8')));
        }
        return $badString;
    }

    /**
     * Cleaning input value
     * @param input value
     * @return string|string[]
     */
    function cleanInput($input)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
        );
        return preg_replace($search, '', $input);
    }

    function sqlPrevents($input)
    {
        if (is_array($input)) {
            foreach ($input as $var => $val) {
                $output[$var] = $this->sqlPrevents($val);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $input = stripslashes($input);
            }
            $input  = $this->cleanInput($input);
            $output = mysql_real_escape_string($input);
        }
        return $output;
    }

    /**
     * Make Sql Where Query From Filter data
     * @param $type
     * @param $key
     * @param $filterArray
     * @param $query
     * @return mixed $where return sql where condition
     */
    public function createWhere($type, $key, $filterArray, $query)
    {
        if (empty($filterArray)) {
            return $query;
        }
        $match = $filterArray['type'] ?? '';

        if (isset($filterArray['filterType']) && $filterArray['filterType'] == "date") {
            $filterArray['filter'] = 0;
        }

        if (isset($filterArray['filterType']) && $filterArray['filterType'] == "set") {
            $filterArray['filter'] = $filterArray['values'];
        }

        $value = (!empty($filterArray['filter']) || $filterArray['filter'] == 0) ? $filterArray['filter'] : (is_array($filterArray['filter']) ? $filterArray['filter'] : "");

        switch ($type) {
            case "text": //if filter type will be text so it goes here
                $value = strip_tags($value);
                $value = str_replace('`', '', $value);
                $value = str_replace("'", "", $value);
                $value = str_replace('%', '\%', $value);
                if ($match == "contains") {
                    $query = $query->where($key, 'LIKE', '%' . $value . '%');
                } else if ($match == "notContains") {
                    $query = $query->where($key, 'NOT LIKE', '%' . $value . '%');
                } else if ($match == "equals") {
                    $query = $query->where($key, '=', $value);
                } else if ($match == "notEqual") {
                    $query = $query->where($key, '!=', $value);
                } else if ($match == "startsWith") {
                    $query = $query->where($key, 'LIKE', $value . '%');
                } else if ($match == "endsWith") {
                    $query = $query->where($key, 'LIKE', '%' . $value);
                }
                break;
            case "number": //if filter type will be number so it goes here
                $value   = (!empty($filterArray['filter'])) ? $filterArray['filter'] : 0;
                $valueTo = (!empty($filterArray['filterTo'])) ? $filterArray['filterTo'] : 0;
                if ($match == "equals") {
                    $query = $query->where("$key", '=', $value);
                } else if ($match == "notEqual") {
                    $query = $query->where("$key", '!=', $value);
                } else if ($match == "lessThan") {
                    $query = $query->where("$key", '<', $value);
                } else if ($match == "lessThanOrEqual") {
                    $query = $query->where("$key", '<=', $value);
                } else if ($match == "greaterThan") {
                    $query = $query->where("$key", '>', $value);
                } else if ($match == "greaterThanOrEqual") {
                    $query = $query->where("$key", '>=', $value);
                } else if ($match == "inRange") {
                    $query = $query->whereBetween("$key", [$value, $valueTo]);
                }
                break;
            case "date":
                $dateFrom = date('Y-m-d', strtotime($filterArray['dateFrom']));
                $dateTo   = !empty($filterArray['dateTo']) ? date('Y-m-d', strtotime($filterArray['dateTo'])) : null;
                if ($match == "equals") {
                    $query = $query->where(\DB::raw("date_format($key, '%Y-%m-%d')"), '=', $dateFrom);
                } else if ($match == "notEqual") {
                    $query = $query->where(\DB::raw("date_format($key, '%Y-%m-%d')"), '!=', $dateFrom);
                } else if ($match == "lessThan") {
                    $query = $query->where(\DB::raw("date_format($key, '%Y-%m-%d')"), '<', $dateFrom);
                } else if ($match == "greaterThan") {
                    $query = $query->where(\DB::raw("date_format($key, '%Y-%m-%d')"), '>', $dateFrom);
                } else if ($match == "inRange") {
                    $query = $query->whereBetween(\DB::raw("date_format($key, '%Y-%m-%d')"), [$dateFrom, $dateTo]);
                }
                break;

            case "set":
                $query = $query->whereIn($key, $value);
                break;
        }
        return $query;
    }

    public static function base_url()
    {
        return url("/");
    }

    public static function getUploadPath()
    {
        $path = public_path() . '/uploads/';
        if (!is_dir($path)) {
            mkdir($path);       //create the directory
            chmod($path, 0777); //make it writable
        }
        return $path;
    }

    /* To get url of files */
    public static function getUploadUrl()
    {
        return url('uploads/');
    }

    public static function getDefaultUrl()
    {
        return url('uploads/default') . '/';
    }

    public static function getUserUrl()
    {
        return url('uploads/users') . '/';
    }

    public static function getImageSizes()
    {
        return [0 => ["width" => 100, "height" => 100], 1 => ["width" => 250, "height" => 250], 2 => ["width" => 500, "height" => 500], 3 => ['width' => 460, 'height' => 300]];
    }

    public static function getStatus()
    {
        return array(
            0 => "Inactive",
            1 => "Active",
        );
    }

    public static function getOtpForUser($length = 6)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public static function timeElapsedString($datetime, $full = 0)
    {
        $now  = new \DateTime;
        $ago  = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {

            if ($diff->$k && $k != 's') {
                $v = $diff->$k . ' ' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!empty($full)) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public function graphLabel($monthDayCal)
    {
        if ($monthDayCal == 'default_six_month') {
            $months = [];
            for ($i = 0; $i < 6; $i++) {
                $months[] = date('F', strtotime("-$i month"));
            }
            return $months;
        }
    }

    public function getSixMonthArray()
    {
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $months[] = date('M-Y', strtotime("-$i month"));
        }
        return $months;
    }

    public function getSixMonthNullArray()
    {
        return [0, 0, 0, 0, 0, 0];
    }

    public function chartCommonColor()
    {
        return ['colorPink' => '#FFCC00', 'colorGreen' => '#AAD880', 'colorOrange' => '#D88080', 'noColor' => '#808080'];
    }
}
