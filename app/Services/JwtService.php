<?php

namespace App\Services;

use App\Exceptions\XJwtException;

class JwtService
{

    /**
     * @var static
     */
    protected static $instance;

    /**
     * 实例化
     *
     * @return static
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
            $secret = (string)env('JWT_SECRET', '');
            $ttl = (int)env('JWT_TTL', 60);
            if (empty($secret)) {
                throw new XJwtException('jwt secret error');
            }
            static::$instance->secret = $secret;
            static::$instance->ttl = $ttl;
        }

        static::$instance->currentTime = time();

        return static::$instance;
    }

    protected $secret;
    protected $ttl = 60;
    protected $currentTime;

    protected $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
    ];

    protected $payload = [
        'sub' => '',
        'iat' => 0,   // 签发时间
        'exp' => 0,   // 过期时间
    ];


    /**
     * 生成token
     *
     * @param $user_id
     * @return string
     *
     * @author gzy<guozhenyi@kuaixun.tech>
     * @date 2019-03-14
     */
    public function tokenByUserId($user_id)
    {
        return $this->encode($user_id);
    }

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param $sub
     * @return string
     */
    public function encode($sub)
    {
        $header = $this->header;
        $payload = $this->getPayload($sub);

        $segments = [
            $this->urlSafeB64Encode($this->jsonEncode($header)),
            $this->urlSafeB64Encode($this->jsonEncode($payload)),
        ];

        $signature = $this->signature(implode('.', $segments), $header['alg']);

        $segments[] = $this->urlSafeB64Encode($signature);

        return implode('.', $segments);
    }


    /**
     * Decodes a JWT string into a PHP object.
     *
     * @param $token
     * @return array
     * @throws \Exception
     */
    public function decode($token)
    {
        $token = $this->validateStructure($token);

        list($headB64, $bodyB64, $signB64) = explode('.', $token);

        $header = $this->jsonDecode($this->urlSafeB64Decode($headB64), true);

        $this->verifyHeader($header);

        $signature = $this->urlSafeB64Decode($signB64);

        if (!$this->verifySignature($headB64 . '.' . $bodyB64, $signature, $header['alg'])) {
            throw new XJwtException('Signature verification failed');
        }

        $payload = $this->jsonDecode($this->urlSafeB64Decode($bodyB64), true);

        return $payload;
    }


    /**
     * Sign a string with a given key and algorithm.
     *
     * @param $data
     * @param $algo
     *
     * @return string
     */
    public function signature($data, $algo)
    {
        if ($algo == 'HS256') {
            return hash_hmac('SHA256', $data, $this->secret, true);
        }

        throw new XJwtException('Not support algorithm');
    }


    /**
     * Verify signature
     *
     * @param $data
     * @param $signature
     * @param $algo
     *
     * @return bool
     */
    protected function verifySignature($data, $signature, $algo)
    {
        $new_signature = $this->signature($data, $algo);

        return hash_equals($signature, $new_signature);
    }


    protected function verifyHeader(array $header)
    {
        if (empty($header['typ']) || empty($header['alg'])) {
            throw new XJwtException('JWT header error');
        }

        if ($header['alg'] != $this->header['alg']) {
            throw new XJwtException('Algorithm not supported');
        }

        return $header;
    }


    public function verifyPayload(array $payload)
    {
        $hasKeys = array_keys($this->payload);

        foreach ($hasKeys as $k) {
            if (!array_key_exists($k, $payload)) {
                throw new XJwtException('Payload miss parameter');
            }
        }

        if (isset($payload['iat']) && $this->currentTime < $payload['iat']) {
            throw new XJwtException('Issued At (iat) timestamp cannot be in the future');
        }

        // 过期30天，不可自动续token，需重新登录
        if (isset($payload['exp']) && $this->currentTime > ($payload['exp'] + 3600 * 24 * 30)) {
            throw new XJwtException('Token has expired and can no longer be refreshed');
        }

        // Check if this token has expired.
        if (isset($payload['exp']) && $this->currentTime >= $payload['exp']) {
            throw new XJwtException('Token has expired', 40102);
        }

        return $payload;
    }


    /**
     *
     * @param  string  $token
     * @throws \Exception
     * @return string
     */
    protected function validateStructure($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new XJwtException('Wrong number of segments');
        }

        $parts = array_filter(array_map('trim', $parts));

        if (count($parts) !== 3 || implode('.', $parts) !== $token) {
            throw new XJwtException('Malformed token');
        }

        return $token;
    }


    /**
     * Decode a JSON string into a PHP object.
     *
     * @param $input
     * @param bool $assoc
     * @return array|object
     */
    public function jsonDecode($input, $assoc = false)
    {
        $obj = json_decode($input, $assoc, 512, JSON_BIGINT_AS_STRING);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new XJwtException(json_last_error_msg());
        }

        return $obj;
    }

    /**
     * Encode a PHP object into a JSON string.
     *
     * @param array|object $input
     * @return string
     */
    public function jsonEncode($input)
    {
        $json = json_encode($input, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new XJwtException(json_last_error_msg());
        }

        return $json;
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string A decoded string
     */
    public function urlSafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $input .= str_repeat('=', $padLen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    public function urlSafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }


    protected function getPayload($sub)
    {
        $payload = $this->payload;

        $payload['sub'] = $sub;
        $payload['iat'] = $this->currentTime;
        $payload['exp'] = $this->currentTime + $this->ttl * 60;

        return $payload;
    }


    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function __get($name)
    {
        return $this->{$name};
    }




}




