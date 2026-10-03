<?php

namespace App\Helpers;

class CryptoHelper
{
    /**
     * Generates an uppercase collision-resistant quiz code like "QA-7X9B2"
     */
    public static function generateQuizCode(string $prefix = 'QA'): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        $bytes = random_bytes(5);
        $len = strlen($chars);
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[ord($bytes[$i]) % $len];
        }
        return "{$prefix}-{$code}";
    }

    /**
     * Generates an uppercase verifiable certificate code like "CERT-9X2A-7K8F"
     */
    public static function generateCertificateCode(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $part1 = '';
        $part2 = '';
        $bytes = random_bytes(8);
        $len = strlen($chars);
        for ($i = 0; $i < 4; $i++) {
            $part1 .= $chars[ord($bytes[$i]) % $len];
            $part2 .= $chars[ord($bytes[$i + 4]) % $len];
        }
        return "CERT-{$part1}-{$part2}";
    }
}
