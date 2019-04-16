<?php
/**
 * Created by PhpStorm.
 * User: moon
 * Date: 2019-04-16
 * Time: 14:16
 */

namespace Shiran\EasyIp\Providers\Tencent;

use Zttp\Zttp;
use Shiran\EasyIp\Base\Base;
use Shiran\EasyIp\Contracts\Resolvable;
use Shiran\EasyIp\Exception\ReferenceException;

class Tencent extends Base implements Resolvable
{
    const PROVIDER_NAME = 'Tencent';
    const URL = 'https://apis.map.qq.com/ws/location/v1/ip';

    protected $ip;
    protected $response;

    /**
     * @param string $ip
     * @return array
     * @throws \Exception
     */
    public function parse(string $ip)
    {
        $params = [
            'ip' => $ip,
            'key' => $this->config['tencent']['key'],
        ];

        $this->ip = $ip;
        $this->response = Zttp::get(static::URL, $params)->json();

        return $this->check()->format();
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return static::PROVIDER_NAME;
    }

    /**
     * @return $this
     * @throws ReferenceException
     */
    public function check()
    {
        if ($this->response['status'] !== 0) {
            throw new ReferenceException($this->response['message']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function format()
    {
        $result = $this->response['result'];

        return [
            'provider' => static::PROVIDER_NAME,
            'ip' => $this->ip,
            'postcode' => $result['ad_info']['adcode'],
            'country' => $result['ad_info']['nation'],
            'province' => $result['ad_info']['province'],
            'city' => $result['ad_info']['city'],
            'district' => $result['ad_info']['district'],
            'implode' => implode('', array_splice($result['ad_info'], 0, 4)),
            'location' => [
                'latitude' => $result['location']['lat'],
                'longitude' => $result['location']['lng'],
            ],
        ];
    }
}