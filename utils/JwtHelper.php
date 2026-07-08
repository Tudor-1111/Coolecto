<?php

class JwtHelper {
    private static $secret_key = 'AnaAreMerecireselvinesicereDarAnaNuMaiAre'; 

    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode($data) {
        $padding = strlen($data) % 4;
        $paddingData = $padding !== 0 ? str_pad($data, strlen($data) + 4 - $padding, '=') : $data;
        return base64_decode(strtr($paddingData, '-_', '+/'));
    }

    public static function generateToken($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64url_encode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verifyToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $validSignature = self::base64url_encode($signature);

        if (hash_equals($validSignature, $base64UrlSignature)) {
            $payload = json_decode(self::base64url_decode($base64UrlPayload), true);
            
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false; 
            }
            
            return $payload;
        }
        
        return false;
    }
}
?>