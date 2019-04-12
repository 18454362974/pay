<?php
namespace OsPay\Pay;

use Illuminate\Support\Manager;
use OsPay\Pay\Drivers\Alipay\AlipayAuth;
use OsPay\Pay\Drivers\Alipay\Alipay;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use OsPay\Pay\Factory;

/**
 * 驱动
 */
class OsPayManager extends Manager implements Factory
{
	
    protected $app;

    public function __construct()
    {
        $this->app = app();
    }

    /**
     * 调用
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @param    string       $driver 驱动名称
     * @return   [type]               [description]
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * 支付宝认证
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @return   [type]       [description]
     */
    protected function createAliauthDriver()
    {
        $config = $this->app['config']['pay.alipay'];
        return $this->buildProvider(
            AlipayAuth::class, $config
        );
    }

    /**
     * 支付宝支付
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @return   [type]       [description]
     */
    protected function createAlipayDriver()
    {
        $config = $this->app['config']['pay.alipay'];
        return $this->buildProvider(
            Alipay::class, $config
        );
    }

    /**
     * 调用对应驱动
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @param    string       $provider 类
     * @param    array        $config   配置信息
     * @return   [type]                 [description]
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $this->app['request'], 
            $config, 
            $this->formatRedirectUrl($config)
        );
    }

    /**
     * url
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @param    array        $config 配置信息
     * @return   [type]               [description]
     */
    protected function formatRedirectUrl(array $config)
    {
        $redirect = value($config['redirect']);

        return Str::startsWith($redirect, '/')
                    ? $this->app['url']->to($redirect)
                    : $redirect;
    }

    /**
     * 驱动响应
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @return   [type]       [description]
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Pay driver was specified.');
    }

}