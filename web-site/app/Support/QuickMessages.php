<?php

namespace App\Support;

/**
 * Sohbet hızlı yanıtları — kayıt / tanışma odaklı hazır mesajlar.
 */
final class QuickMessages
{
    /** @return list<string> */
    public static function all(): array
    {
        return [
            'Merhaba, nasılsın?',
            'Merhaba! Tanışmak isterim.',
            'Güzel bir gün diliyorum 🌸',
            'Ciddi ve saygılı bir tanışma arıyorum.',
            'Profilin çok güzel, yazmak istedim.',
            'Nerelisin, hangi şehirdesin?',
            'Hobilerin neler?',
            'Uygun olduğunda yazabilirsin.',
            'Tanıştığımıza memnun oldum.',
            'İyi akşamlar, sohbet etmek ister misin?',
        ];
    }

    /** İlk mesaj için daha kısa selam listesi */
    /** @return list<string> */
    public static function greetings(): array
    {
        return [
            'Merhaba, ciddi ve saygılı bir tanışma arıyorum. Nasılsın?',
            'Merhaba! Güzel bir gün diliyorum, tanışmak isterim.',
            'Selam, profilin dikkatimi çekti. Tanışabilir miyiz?',
            'Merhaba, nasılsın? Sohbet etmek isterim.',
        ];
    }

    /**
     * @deprecated GreetingTemplates uyumluluğu
     * @return list<string>
     */
    public static function forThread(bool $isFirstMessage): array
    {
        return $isFirstMessage ? self::greetings() : self::all();
    }
}
