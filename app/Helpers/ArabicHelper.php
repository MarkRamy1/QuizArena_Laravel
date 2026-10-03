<?php

namespace App\Helpers;

class ArabicHelper
{
    /**
     * Normalizes Arabic text for grading and search:
     * - Strips tashkeel (diacritics: fatha, damma, kasra, sukun, tanween, shadda)
     * - Normalizes alef forms (أ, إ, آ, ٱ -> ا)
     * - Normalizes taa marbuta (ة -> ه)
     * - Normalizes alef maqsura (ى -> ي)
     * - Removes tatweel / kashida (ـ)
     * - Trims and unifies redundant whitespaces
     */
    public static function normalize(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = trim(mb_strtolower($text, 'UTF-8'));
        // Remove tashkeel & tatweel
        $text = preg_replace('/[ًٌٍَُِّْـ]/u', '', $text);
        // Normalize alefs
        $text = preg_replace('/[أإآٱ]/u', 'ا', $text);
        // Normalize taa marbuta
        $text = preg_replace('/ة/u', 'ه', $text);
        // Normalize alef maqsura
        $text = preg_replace('/ى/u', 'ي', $text);
        // Normalize spaces
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Compares student answer with model answer(s)
     */
    public static function compareAnswers(?string $studentAnswer, string|array|null $modelAnswer): bool
    {
        if (empty($studentAnswer) || empty($modelAnswer)) {
            return false;
        }

        $cleanStudent = self::normalize($studentAnswer);
        if ($cleanStudent === '') {
            return false;
        }

        if (is_array($modelAnswer)) {
            foreach ($modelAnswer as $ans) {
                if (self::normalize($ans) === $cleanStudent) {
                    return true;
                }
            }
            return false;
        }

        return $cleanStudent === self::normalize($modelAnswer);
    }
}
