<?php

namespace App\Domain;

class CommissionStatus
{
    const APPROVED      = "APPROVED";

    const PENDING       = "PENDING";

    const PAID          = "PAID";

    const SHAVED        = "SHAVED";

    public static function all()
    {
        return [
            self::APPROVED      => self::APPROVED,
            self::PENDING       => self::PENDING,
            self::PAID          => self::PAID,
            self::SHAVED        => self::SHAVED
        ];
    }

}
