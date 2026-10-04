<?php

namespace App\Support;

class WhatsApp
{
    /**
     * Build a wa.me deep link with a pre-filled message.
     */
    public static function url(string $phone, string $message): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        return "https://wa.me/{$digits}?text=" . rawurlencode($message);
    }

    public static function businessUrl(string $message): string
    {
        return self::url(config('whatsapp.number'), $message);
    }
}
