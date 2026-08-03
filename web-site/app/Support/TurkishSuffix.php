<?php

namespace App\Support;

/**
 * Türkçe ünlü uyumu kuralına göre hal eklerini üretir.
 * -da/-de, -dan/-den, -ta/-te, -tan/-ten
 */
final class TurkishSuffix
{
    /**
     * Kelimenin son ünlüsüne göre locative (-da/-de/-ta/-te) eki döndürür.
     */
    public static function locative(string $word): string
    {
        return self::suffixWithApostrophe($word, self::locativeSuffix($word));
    }

    /**
     * Kelimenin son ünlüsüne göre ablative (-dan/-den/-tan/-ten) eki döndürür.
     */
    public static function ablative(string $word): string
    {
        return self::suffixWithApostrophe($word, self::ablativeSuffix($word));
    }

    /**
     * "İzmir'de" formatında döndürür (kesme işareti ile).
     */
    private static function suffixWithApostrophe(string $word, string $suffix): string
    {
        // Özel adlar için kesme işareti
        if (preg_match('/[A-Za-zÇĞİıÖŞÜçğıöşü]/u', $word)) {
            return $word . "'" . $suffix;
        }
        return $word . $suffix;
    }

    /**
     * Son ünlüyü bulur (Türkçe harfler dahil).
     */
    private static function lastVowel(string $word): string
    {
        // Türkçe küçük harfe çevir
        $lower = mb_strtolower($word, 'UTF-8');
        // Türkçe ünlüler
        preg_match_all('/[aeıioöuü]/u', $lower, $matches);
        if (empty($matches[0])) {
            return 'a'; // default
        }
        return end($matches[0]);
    }

    /**
     * Ünlü uyumuna göre -da/-de veya -ta/-te eki.
     */
    private static function locativeSuffix(string $word): string
    {
        $vowel = self::lastVowel($word);
        $lastChar = mb_substr($word, -1, 1, 'UTF-8');
        
        // Sert ünsüzlerle bitiyorsa -ta/-te
        $hardEndings = ['p', 'ç', 't', 'k', 'f', 'h', 's', 'ş'];
        $isHard = in_array(mb_strtolower($lastChar, 'UTF-8'), $hardEndings);
        
        // Ön ünlüler (e, i, ö, ü) → -de/-te
        // Arka ünlüler (a, ı, o, u) → -da/-ta
        $isFront = in_array($vowel, ['e', 'i', 'ö', 'ü']);
        
        if ($isHard) {
            return $isFront ? 'te' : 'ta';
        }
        return $isFront ? 'de' : 'da';
    }

    /**
     * Ünlü uyumuna göre -dan/-den veya -tan/-ten eki.
     */
    private static function ablativeSuffix(string $word): string
    {
        $vowel = self::lastVowel($word);
        $lastChar = mb_substr($word, -1, 1, 'UTF-8');
        
        $hardEndings = ['p', 'ç', 't', 'k', 'f', 'h', 's', 'ş'];
        $isHard = in_array(mb_strtolower($lastChar, 'UTF-8'), $hardEndings);
        
        $isFront = in_array($vowel, ['e', 'i', 'ö', 'ü']);
        
        if ($isHard) {
            return $isFront ? 'ten' : 'tan';
        }
        return $isFront ? 'den' : 'dan';
    }
}
