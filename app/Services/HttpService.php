<?php
/**
 * Http 请求 第二版
 * @author: gzy <gzyonline@hotmail.com>
 * @date: 2017-4-24
 * @time: 15:19
 * @Version: v2.2
 *
 * history:
 *   2.1
 *     改进：返回请求头
 *   2.2
 *     增加reqBody方法
 *
 */

namespace App\Services;

class HttpService
{

    /**
     * @var static
     */
    protected static $instance;


    private function __construct(){}

    public function __clone()
    {
        trigger_error('本类应用单例模式', E_USER_ERROR);
    }


    /**
     * 实例化
     *
     * @return static
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }


    /**
     * http请求JSON数据
     *
     * @param string $host
     * @param string $query
     * @param string $method
     * @param array $data
     * @param array $headers
     * @param bool $jsonAssoc  如果为true时返回array,为false时返回object
     * @return array | object
     * @throws \Exception
     */
    public function reqJson($host, $query, $method = 'GET', array $data = [], array $headers = [], $jsonAssoc = false)
    {
        $result = $this->reqText($host, $query, $method, $data, $headers);

        $json = json_decode($result['content'], (bool) $jsonAssoc);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \UnexpectedValueException('ServiceJsonFormatException', 500);
        }

        return $json;
    }


    /**
     * http请求
     *
     * @param string $host
     * @param string $query
     * @param string $method
     * @param array $data
     * @param array $headers
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function reqText($host, $query, $method = 'GET', array $data = [], array $headers = [], $content = null)
    {
        $fullUrl = rtrim($host, '/') . '/' . trim($query, '/');

        $result = $this->req($method, $fullUrl, $data, $headers, $content);

        return $result;
    }


    /**
     * HTTP请求方法
     *
     * 默认发送类型为 application/json
     *
     * @param string $method
     * @param string $fullUrl
     * @param array $data
     * @param array $headers
     * @param string $content
     * @param bool $ca
     * @param int $timeout
     * @return array
     * @throws \Exception
     */
    public function req($method, $fullUrl, array $data = [], array $headers = [], $content = null, $ca = false, $timeout = 30)
    {
        $method = strtoupper(trim($method));

        $cacert = getcwd() .'/cacert.pem'; // CA根证书
        if (! file_exists($cacert) || ! is_readable($cacert)) {
            $ca = false;
        }

        $ssl = substr($fullUrl, 0, 8) == 'https://' ? true : false;

        $ch = curl_init();

        if ($ssl && $ca) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);      // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // 检查公用名是否存在，并且是否与提供的主机名匹配
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁止cURL验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // 不检查公用名
        }

        curl_setopt($ch, CURLOPT_URL, $fullUrl);        // 地址
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // 跟踪301自动跳转
        curl_setopt($ch, CURLOPT_HEADER, true);        // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取的信息以字符串的形式返回
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);    // 设置超时限制防止死循环

        // 解决POST的参数内容长度超过1024时无法获得response的数据的问题,其实是因为curl会返回 HTTP/1.1 100 Continue
//        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);
        $headers['Expect'] = '';

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);       // 发送一个常规的Post请求
//            $headers['Content-type'] = 'application/x-www-form-urlencoded';
        } elseif ($method != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        // 格式化请求头
        foreach ($headers as $k => $val) {
            unset($headers[$k]);
            $headers[strtolower($k)] = $val;
        }

        if ($method != 'GET') {
            if (is_null($content)) {
                $headers['content-type'] = 'application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            }
//            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
        }

        $aryHeader = [];
        foreach ($headers as $key => $value) {
            $aryHeader[] = $key .':'. $value;
        }
        if (count($aryHeader) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aryHeader); // 设置HTTP头字段的数组
        }

        $string = curl_exec($ch);

        /*
         * 捕获cURL错误
         */
        if ($errNo = curl_errno($ch)) {
            throw new \UnexpectedValueException(curl_strerror($errNo), 500);
//            throw new UnexpectedValueException(curl_error($ch), 500); // 这个会暴露接口地址
        }

        /*
         * 捕获HTTP异常
         */
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != '200') {
            throw new \UnexpectedValueException('ServiceException:'. $httpCode, 500);
        }

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);;
        if (empty($contentType)) {
            $contentType = 'text/html;charset=utf-8';
        }

        curl_close($ch); // 结束cURL会话

        list($header, $body) = explode("\r\n\r\n", $string, 2);

        return [
            'headers'=> [
                'content-type' => $contentType
            ],
            'header' => $header,
            'content'=> $body
        ];

        // curl_setopt($ch, CURLOPT_AUTOREFERER, true); // 自动设置Referer
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟踪301自动跳转

        // curl_setopt($ch, CURLOPT_REFERER, 'http://example.com'); // 构造来路

        // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 Firefox/42.0'); // 模拟用户使用的浏览器

        // curl_setopt($ch, CURLOPT_HTTPHEADER, array( // 设置HTTP头字段的数组
        //     'Content-type:text/html; charset=utf-8',
        //     'X-FORWARDED-FOR: 127.0.0.1',
        //     'CLIENT-IP: 127.0.0.1',
        // ));

    }


    /**
     * HTTP请求方法
     *
     * @param string $method
     * @param string $fullUrl
     * @param array $data
     * @param array $headers
     * @param string $content
     * @param bool $ca
     * @param int $timeout
     * @return array
     * @throws \Exception
     */
    public function reqBody($method, $fullUrl, array $data = [], array $headers = [], $content = null, $ca = false, $timeout = 30)
    {
        $method = strtoupper(trim($method));

        $cacert = getcwd() .'/cacert.pem'; // CA根证书
        if (! file_exists($cacert) || ! is_readable($cacert)) {
            $ca = false;
        }

        $ssl = substr($fullUrl, 0, 8) == 'https://' ? true : false;

        $ch = curl_init();

        if ($ssl && $ca) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);      // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // 检查公用名是否存在，并且是否与提供的主机名匹配
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁止cURL验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // 不检查公用名
        }

        curl_setopt($ch, CURLOPT_URL, $fullUrl);        // 地址
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // 跟踪301自动跳转
//        curl_setopt($ch, CURLOPT_HEADER, true);        // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取的信息以字符串的形式返回
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);    // 设置超时限制防止死循环

//        curl_setopt($ch, CURLOPT_COOKIEFILE, storage_path() . '/cookie.txt');
//        curl_setopt($ch, CURLOPT_COOKIEJAR, storage_path() . '/cookie.txt');

        // 解决POST的参数内容长度超过1024时无法获得response的数据的问题,其实是因为curl会返回 HTTP/1.1 100 Continue
        $headers['Expect'] = '';

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);       // 发送一个常规的Post请求
        } elseif ($method != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        // 格式化请求头，去重
        foreach ($headers as $k => $val) {
            unset($headers[$k]);
            $headers[strtolower($k)] = $val;
        }

        if (!isset($headers['content-type'])) {
            $headers['content-type'] = 'application/json';
        }

        if ($method != 'GET') {
            if (is_null($content) && !empty($data)) {
                if (stripos($headers['content-type'], 'json') !== false) {
                    $content = json_encode($data, JSON_UNESCAPED_UNICODE);
                } else {
                    $content = http_build_query($data);
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }

        $aryHeader = [];
        foreach ($headers as $key => $value) {
            $aryHeader[] = $key .':'. $value;
        }
        if (count($aryHeader) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aryHeader); // 设置HTTP头字段的数组
        }

        $resContent = curl_exec($ch);

        // 捕获cURL错误
        if ($errNo = curl_errno($ch)) {
            throw new \UnexpectedValueException(curl_strerror($errNo), 500);
//            throw new UnexpectedValueException(curl_error($ch), 500); // 这个会暴露接口地址
        }

        // 捕获HTTP异常
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != '200') {
            throw new \UnexpectedValueException('ServiceException:'. $httpCode, 500);
        }

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);;
        if (empty($contentType)) {
            $contentType = 'text/html;charset=utf-8';
        }

        curl_close($ch); // 结束cURL会话

        return [
            'headers'=> [
                'content-type' => $contentType
            ],
            'body'=> $resContent
        ];
    }


}
