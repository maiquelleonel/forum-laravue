<?php

namespace App\Domain;

class BundleCategory
{
    const PADRAO        = "default";

    const UPSELL        = "upsell";

    const PROMOCIONAL   = "promotional";

    const REMARKETING   = "remarketing";

    public static function all()
    {
        return [
            self::PADRAO        => self::PADRAO,
            self::UPSELL        => self::UPSELL,
            self::PROMOCIONAL   => self::PROMOCIONAL,
            //self::REMARKETING   => self::REMARKETING,
        ];
    }

    public static function labels()
    {
        return [
            self::PADRAO        => "Padrão",
            self::UPSELL        => "Upsell",
            self::PROMOCIONAL   => "Promocional",
            //self::REMARKETING   => "Remarketing",
        ];
    }
}