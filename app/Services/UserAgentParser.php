<?php

namespace App\Services;

class UserAgentParser
{
    /**
     * Detect device type from user agent string.
     */
    public static function getDeviceType(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (preg_match('/(bot|crawler|spider|curl|wget)/i', $userAgent)) {
            return 'Bot';
        }

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/(mobile|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop)/i', $userAgent)) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    /**
     * Detect browser name from user agent string.
     */
    public static function getBrowser(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (preg_match('/Edg/i', $userAgent)) {
            return 'Edge';
        }

        if (preg_match('/OPR|Opera/i', $userAgent)) {
            return 'Opera';
        }

        if (preg_match('/Chrome/i', $userAgent)) {
            return 'Chrome';
        }

        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }

        if (preg_match('/Safari/i', $userAgent) && ! preg_match('/Chrome/i', $userAgent)) {
            return 'Safari';
        }

        return 'Other';
    }

    /**
     * Detect operating system / platform from user agent string.
     */
    public static function getPlatform(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (preg_match('/windows nt 10/i', $userAgent)) {
            return 'Windows 10/11';
        }

        if (preg_match('/windows/i', $userAgent)) {
            return 'Windows';
        }

        if (preg_match('/android/i', $userAgent)) {
            return 'Android';
        }

        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'iOS';
        }

        if (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        }

        if (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        }

        return 'Other';
    }
}
