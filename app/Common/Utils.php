<?php

declare(strict_types=1);

namespace App\Common;

use App\Caches\UsersCache;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Utils\Str;
use Hyperf\DbConnection\Db;
use App\Constants\Consts;
use App\Model\UsersWallet;

class Utils
{
    /**
     * @deprecated Use getClientKey() instead.
     */
    const KEY = '';

    public static function getClientKey(): string
    {
        return env('UTILS_CLIENT_KEY', '');
    }

    /**
     * @deprecated Use bcrypt_hash() for new passwords and verifyPasswordWithLegacyFallback() for verification.
     *
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function getRealPassword(string $password, string $salt): string
    {
        $secretKey = env('UTILS_SECRET_KEY', '');
        return md5($secretKey . $password . md5($salt));
    }

    /**
     * Detect whether a stored hash is a legacy (non-bcrypt) format.
     */
    public static function isLegacyHash(string $hash): bool
    {
        return !str_starts_with($hash, '$2y$')
            && !str_starts_with($hash, '$2a$')
            && !str_starts_with($hash, '$2b$');
    }

    /**
     * Verify a password against a stored hash, trying bcrypt first, then legacy MD5 schemes.
     *
     * @return bool
     */
    public static function verifyPasswordWithLegacyFallback(string $password, string $storedHash): bool
    {
        if (!self::isLegacyHash($storedHash)) {
            return self::bcrypt_verify($password, $storedHash);
        }

        if (strlen($storedHash) !== 32 || !ctype_xdigit($storedHash)) {
            return false;
        }

        $secretKey = env('UTILS_SECRET_KEY', '');

        // Legacy scheme: getRealPassword with empty salt
        if (hash_equals($storedHash, md5($secretKey . $password . md5('')))) {
            return true;
        }

        // Legacy scheme: getRealPassword with secretKey as salt
        if ($secretKey !== '' && hash_equals($storedHash, md5($secretKey . $password . md5($secretKey)))) {
            return true;
        }

        // Legacy scheme: MakePassword type 0
        $salt0 = 'ABCDEFG';
        foreach (str_split($password) as $char) {
            $salt0 .= md5($char);
        }
        if (hash_equals($storedHash, md5($salt0))) {
            return true;
        }

        // Legacy scheme: MakePassword type 1
        if (hash_equals($storedHash, md5('TPSHOP' . $password))) {
            return true;
        }

        return false;
    }

    /**
     * 生成密钥
     * @return string
     */
    public static function getSalt(): string
    {
        return Str::random(32);
    }

    /**
     * 生成 token
     * @return string
     */
    public static function getToken(): string
    {
        return Str::random(32);
    }

    /**
     * 随机长度字符串
     * @param int $length
     * @return string
     */
    public static function randString(int $length): string
    {
        return Str::random($length);
    }

    /**
     * 将 URL 或路径规范化为仅存储路径（域名后方的部分）
     * 例如 http://static.fans.local/./touxiang/xxx.jpg -> /touxiang/xxx.jpg
     */
    public static function toStoragePath(string $urlOrPath): string
    {
        $urlOrPath = trim($urlOrPath);
        if ($urlOrPath === '') {
            return '';
        }
        if (preg_match('#^https?://[^/]+(.*)$#', $urlOrPath, $m)) {
            $path = $m[1];
            if ($path === '' || $path === '/') {
                return '';
            }
            $path = ltrim($path, './');
            return $path === '' ? '' : '/' . $path;
        }
        $path = ltrim($urlOrPath, './');
        return $path === '' ? '' : ($path[0] === '/' ? $path : '/' . $path);
    }

    /**
     * 合并显示图片
     * @param string $content
     * @param string $host
     * @return string
     */
    public static function mergeImageUrl(string $content, string $host): string
    {
        $suffix = 'http://' . $host;
        $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        return preg_replace($pregRule, '<img src="' . $suffix . '${1}" style="max-width:100%">', $content);
    }

    /**
     * 判断是否是手机号码
     * @param string $mobile
     * @return bool
     */
    public static function isMobile(string $mobile): bool
    {
        if (preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/', $mobile) === false) {
            return false;
        }
        return true;
    }

    private static ?array $trustedProxies = null;

    private static function getTrustedProxies(): array
    {
        if (self::$trustedProxies === null) {
            $raw = env('TRUSTED_PROXIES', '127.0.0.1,::1');
            self::$trustedProxies = array_map('trim', explode(',', $raw));
        }
        return self::$trustedProxies;
    }

    private static function isTrustedProxy(string $remoteAddr): bool
    {
        foreach (self::getTrustedProxies() as $trusted) {
            if ($trusted === '') {
                continue;
            }
            if (strpos($trusted, '/') !== false) {
                if (self::ipInCidr($remoteAddr, $trusted)) {
                    return true;
                }
            } elseif ($remoteAddr === $trusted) {
                return true;
            }
        }
        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }
        $mask = str_repeat("\xff", (int) ($bits / 8));
        if ($bits % 8) {
            $mask .= chr(0xff << (8 - ($bits % 8)));
        }
        $mask = str_pad($mask, strlen($ipBin), "\x00");
        return ($ipBin & $mask) === ($subnetBin & $mask);
    }

    /**
     * Extract client IP from forwarded headers (only when remote_addr is a trusted proxy).
     */
    private static function extractForwardedIP(array $headers): ?string
    {
        $keys = [
            'x-forwarded-for',
            'http_x_forwarded_for',
            'x-real-ip',
        ];
        foreach ($keys as $k) {
            if (isset($headers[$k])) {
                $ip = trim(explode(',', $headers[$k][0])[0]);
                if ($ip !== '') {
                    return $ip;
                }
            }
        }
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    public static function clientIP(RequestInterface $request): string
    {
        $remoteAddr = $request->getServerParams()['remote_addr'] ?? '';
        if ($remoteAddr !== '' && self::isTrustedProxy($remoteAddr)) {
            $forwarded = self::extractForwardedIP($request->getHeaders());
            if ($forwarded !== null) {
                return $forwarded;
            }
        }
        return $remoteAddr;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    public static function ClientIPByPSR(ServerRequestInterface $request): string
    {
        $remoteAddr = $request->getServerParams()['remote_addr'] ?? '';
        if ($remoteAddr !== '' && self::isTrustedProxy($remoteAddr)) {
            $forwarded = self::extractForwardedIP($request->getHeaders());
            if ($forwarded !== null) {
                return $forwarded;
            }
        }
        return $remoteAddr;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    public static function clientIPList(RequestInterface $request): array
    {
        $remoteAddr = $request->getServerParams()['remote_addr'] ?? '';
        $iArr = [];

        if ($remoteAddr !== '' && self::isTrustedProxy($remoteAddr)) {
            $keys = [
                'x-forwarded-for',
                'http_x_forwarded_for',
                'x-real-ip',
            ];
            $headers = $request->getHeaders();
            foreach ($keys as $k) {
                if (isset($headers[$k])) {
                    $iArr[] = implode(',', $headers[$k]);
                }
            }
        }

        if ($remoteAddr) {
            $iArr[] = $remoteAddr;
        }

        return $iArr;
    }


    public static function ChangeMessage(string $message, $request = null): string
    {
        return $message;
    }

    static function callInterfaceCommon($URL, $type, $params, $headers)
    {
        $ch = curl_init($URL);
        $timeout = 5;
        if ($headers != "") {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);


        switch ($type) {
            case "GET":
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
        }
        $file_contents = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $file_contents;
    }


    static function ToFix($number)
    {
        return bcdiv((string)$number, "1", 2);
    }

    static function CheckError($username)
    {
        $client = Cache::get();
        $count = $client->get("error_password" . $username);
        $count = (int)$count;
        if (!$count) {
            $client->set("error_password" . $username, 1);
        } else {
            if ($count + 1 >= 5) {
                Db::update("update user set status=2,allow_withdraw=2,allow_transfer=2 where username=?", [$username]);
                return false;
            } else {
                $client->set("error_password" . $username, $count + 1);
            }
        }
        return true;
    }

    static function Sign($list)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $appKey = env('UTILS_APP_KEY', '');
        $sign = strtoupper(md5($md5str . "appKey=" . $appKey));
        return $sign;
    }

    /**
     * @deprecated Use bcrypt_hash() instead.
     */
    public static function MakePassword($password, $type = 0)
    {
        if ($type == 0) {
            $salt = 'ABCDEFG';
            $passwordChars = str_split($password);
            foreach ($passwordChars as $char) {
                $salt .= md5($char);
            }
        } else {
            $salt = 'TPSHOP' . $password;
        }
        return md5($salt);
    }
    public static function generate_password($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $password = "";
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
    public static function getExtensionCode($length = 5)
    {
        $code = self::generate_password($length);
        $has = Db::Table("users")->where("extension_code", $code)->first();
        if ($has) {
            $code = self::getExtensionCode();
        }
        return $code;
    }






    /**
     * Generate a Synapse-compatible bcrypt password hash in PHP.
     *
     * Behavior:
     *  - Unicode normalize password with NFKC (uses ext-intl Normalizer).
     *  - Append the global pepper (plain string).
     *  - Use bcrypt with provided cost (rounds).
     *
     * @param string $password Plain text password.
     * @param string $pepper   Global pepper (from config).
     * @param int    $rounds   bcrypt cost (e.g. 12). Matches bcrypt.gensalt(rounds).
     * @return string          bcrypt hash (ASCII string).
     * @throws RuntimeException if intl Normalizer is not available or hash fails.
     */
    static public  function bcrypt_hash(string $password, string $pepper = '', int $rounds = 12): string
    {
        // Require ext-intl for proper Unicode NFKC normalization.
        if (!class_exists(\Normalizer::class)) {
            throw new \RuntimeException('PHP ext-intl (Normalizer) is required for NFKC normalization.');
        }

        // Normalize (NFKC)
        $pw = \Normalizer::normalize($password, \Normalizer::FORM_KC);
        if ($pw === false) {
            // fall back to original password if normalization fails (very unlikely)
            $pw = $password;
        }

        // Append pepper (same as Python: pw.encode("utf8") + pepper.encode("utf8"))
        $tobechashed = $pw . $pepper;

        // Use password_hash with bcrypt. 'cost' corresponds to rounds.
        $options = ['cost' => $rounds];

        $hash = password_hash($tobechashed, PASSWORD_BCRYPT, $options);
        if ($hash === false) {
            throw new \RuntimeException('password_hash failed.');
        }

        return $hash;
    }

    /**
     * Verify a plain password against stored bcrypt hash (Synapse style).
     *
     * @param string $password   Plain password input.
     * @param string $storedHash Stored bcrypt hash (from DB).
     * @param string $pepper     Global pepper (same one used at hash time).
     * @return bool
     */
    static public function bcrypt_verify(string $password, string $storedHash, string $pepper = ''): bool
    {
        if (!class_exists(\Normalizer::class)) {
            throw new \RuntimeException('PHP ext-intl (Normalizer) is required for NFKC normalization.');
        }

        $pw = \Normalizer::normalize($password, \Normalizer::FORM_KC);
        if ($pw === false) {
            $pw = $password;
        }

        return password_verify($pw . $pepper, $storedHash);
    }

    static public function random_string(int $length = 10): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $result;
    }
    static public function new_device()
    {
        return  strtoupper(self::random_string(10));
    }


    static public  function base62_encode(int $num, int $minWidth = 0): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = 62;
        $encoded = '';

        if ($num === 0) {
            $encoded = '0';
        } else {
            $num = $num & 0xFFFFFFFF;
            while ($num > 0) {
                $encoded = $chars[$num % $base] . $encoded;
                $num = intdiv($num, $base);
            }
        }

        return str_pad($encoded, $minWidth, '0', STR_PAD_LEFT);
    }

    /**
     * 去除 padding 的 base64（unpaddedbase64.encode_base64）
     */
    static public function unpadded_base64_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * 完整实现：生成 Synapse 风格的 ID
     */
    static public  function generate_synapse_style_id(string $localpart): string
    {
        $b64local = self::unpadded_base64_encode($localpart);
        $randomString = self::random_string(20);
        $base = "syr_{$b64local}_{$randomString}";

        $crc = crc32($base);
        $crcBase62 = self::base62_encode($crc, 6);

        return "{$base}_{$crcBase62}";
    }
}
