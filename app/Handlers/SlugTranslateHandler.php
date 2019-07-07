<?php


namespace App\Handlers;


use GuzzleHttp\Client;

class SlugTranslateHandler
{
    private $appid = '';

    private $key = '';

    private $api = 'http://api.fanyi.baidu.com/api/trans/vip/translate?';

    public function __construct()
    {
        $this->appid = config('services.baidu_translate.appid');
        $this->key = config('services.baidu_translate.key');
    }

    public function translate($text)
    {
        // 如果没有配置百度翻译，自动使用兼容的拼音方案
        if (empty($appid) || empty($key)) {
            return $this->pinyin($text);
        }

        $http = new Client();

        $sign = md5($appid . $text . time() . $key);

        $query = http_build_query([
            "q" => $text,
            "from" => "zh",
            "to" => "en",
            "appid" => $appid,
            "salt" => time(),
            "sign" => $sign,
        ]);

        $response = $http->get($this->api . $query);

        $result = json_decode($response->getBody(), true);


        // 尝试获取获取翻译结果
        if (isset($result['trans_result'][0]['dst'])) {
            return str_slug($result['trans_result'][0]['dst']);
        } else {
            // 如果百度翻译没有结果，使用拼音作为后备计划。
            return $this->pinyin($text);
        }

    }

    public function pinyin($text)
    {
        return str_slug(app(Pinyin::class)->permalink($text));
    }
}