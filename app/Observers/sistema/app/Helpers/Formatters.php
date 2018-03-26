<?php

if(!function_exists("get_value")){
    /**
     * Get Value from Object/Array
     * @param $object
     * @param $value
     * @param null $default
     * @return null
     */
    function get_value($object, $value, $default = null)
    {
        if (is_object($object)) {
            return isset($object->$value) ? $object->$value : $default;
        }

        if (is_array($object)) {
            return isset($object[$value]) ? $object[$value] : $default;
        }

        return $default;
    }
}

if(!function_exists("percent")){
    function percent($totalValue, $partialValue)
    {
        if ($totalValue > 0) {
            return number_format(($partialValue * 100) / $totalValue, 2);
        }
        return number_format(0, 2);
    }
}

if(!function_exists("strip_accents")){
    function strip_accents($word)
    {
        return preg_replace([
            "/(á|à|ã|â|ä)/",
            "/(Á|À|Ã|Â|Ä)/",
            "/(é|è|ê|ë)/",
            "/(É|È|Ê|Ë)/",
            "/(í|ì|î|ï)/",
            "/(Í|Ì|Î|Ï)/",
            "/(ó|ò|õ|ô|ö)/",
            "/(Ó|Ò|Õ|Ô|Ö)/",
            "/(ú|ù|û|ü)/",
            "/(Ú|Ù|Û|Ü)/",
            "/(ñ)/",
            "/(Ñ)/"
        ],
            explode(" ","a A e E i I o O u U n N"),
            $word
        );
    }
}

if(!function_exists("humanize")){
    function humanize($string)
    {
        if (trans("validation.attributes.$string") != "validation.attributes.$string") {
            return trans("validation.attributes.$string");
        }

        $string = str_ireplace(["-", "_"], " ", $string);
        return ucfirst( $string );
    }
}

if(!function_exists("build_url")){
    function build_url($scheme, $domain, $path="", $query="")
    {
        if(!ends_with("/", $domain) && !starts_with("/", $path)){
            $path = "/{$path}/";
        }

        if($query){
            $query = "?{$query}";
        }

        return $scheme."://". str_ireplace("//", "/", $domain.$path.$query);
    }
}