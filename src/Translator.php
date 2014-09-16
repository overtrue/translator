<?php

namespace Overtrue;

/**
 * 语言本地化工具
 *
 * <pre>
 *
 * 1.目录结构：
 * app/
 * |-- i18n/
 * |    |-- zh_CN/
 * |    |    |-- all.php   # return array('key' => 'pattern');
 * |    |-- en_US/
 * ...
 *
 * 2.在字符串中使用变量:
 *
 * <?php
 * return array(
 *    'user_not_exists' => 'use {name} not exists.',
 *    ...
 * );
 *
 * 3. 使用Translator:
 *
 * <?php
 *
 * use Overtrue\Translator;
 *
 * $translator = new Translator($appPath . '/i18n', 'zh_CN');
 *
 * //格式化语言包里的key
 * echo $translator->trans('user_not_exists.', ['name' => $username]);
 *
 * //格式化指定的字符串：
 * echo $translator->format('user {name} not exists.', ['name' => $username]);
 *
 * </pre>
 */
class Translator
{
    /**
     * 设置语言
     *
     * @var string
     */
    protected $locale;

    /**
     * 语言配置
     *
     * @var array
     */
    protected $patterns;

    /**
     * 语言包基础目录
     *
     * @var string
     */
    protected $basePath;


    /**
     * constructor
     *
     * 设置默认语言与语言包目录
     *
     * @param string $languageDir， ex: APP_PATH . '/i18n'
     * @param string $locale        ex: zh_CN
     */
    public function __construct($languageDir, $locale = '')
    {
        if (!stream_resolve_include_path($languageDir)
            || !is_dir($languageDir)) {
            throw new Exception("语言包目录 '$languageDir' 不存在或不可读。");
        }

        $this->basePath = trim($languageDir, '/') . '/';

        $locale = $locale ? : $this->getBaseLangauge();

        $this->setLocale($locale);
    }

   /**
     * 设置语言
     *
     * @param string $locale 语言，ex:zh_Hant_TW、 zh_CN、en_US
     *
     * @return  $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        $this->patterns[$locale] = $this->loadPatterns($locale);

        return $this;
    }


    /**
     * 格式化
     *
     * @param string $pattern
     * @param array  $data
     *
     * @return string
     */
    public function format($pattern, $data = [])
    {
        is_array($data) || $data = array($data);

        $keys = array_map(function($key){
            return "{{$key}}";
        }, array_keys($data));

        return str_replace($keys, $data, $pattern);
    }

    /**
     * 获取语言
     *
     * @param string $name
     * @param array  $data
     *
     * @return string|array
     */
    public function trans($name, $data = [])
    {
        if (is_array($name)) {
            $strings = [];
            foreach ($name as $key => $data) {
                $strings[$key] = $this->getLine($key, $data);
            }

            return $string;
        }

        return $this->getLine($name, (array) $data);
    }

    /**
     * 获取一条语句的翻译结果
     *
     * @param string $key
     * @param array  $data
     * @param string $locale
     *
     * @return string
     */
    public function getLine($key, $data = [], $locale = '')
    {
        $pattern = $this->getPattern($key, $locale);

        if (empty($pattern)) {
            return $key;
        }

        return empty($data) ? $pattern : $this->format($pattern, (array) $data);
    }

    /**
     * 获取最佳语言
     *
     * 如果为空返回: 'zh_CN'
     *
     * @return string
     */
    protected function getBaseLangauge()
    {
        $langs = explode(",",str_replace("-","_",$_SERVER["HTTP_ACCEPT_LANGUAGE"]));

        if (empty($langs[0])) {
            return 'zh_CN';
        }

        return $langs[0];
    }

    /**
     * 获取语言配置项中原始key
     *
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    protected function getPattern($name, $locale = '')
    {
        $locale = $locale ? : $this->locale;

        if (!$this->localeIsLoaded($locale)) {
            $this->patterns[$locale] = $this->loadPatterns($locale);
        }

        return array_get($this->patterns[$locale], $name);
    }

    /**
     * 从数组取值，支持'xxx.xxx' 形式访问
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function arrayGet($array, $key, $default)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default instanceof Closure ? $default() : $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * 是否加载过对应语言的语言包
     *
     * @param string $locale
     *
     * @return boolean
     */
    protected function localeIsLoaded($locale)
    {
        return isset($this->patterns[$locale]);
    }

    /**
     * 读取指定语言的语言包
     *
     * @param string $locale
     *
     * @return array|boolean
     */
    protected function loadPatterns($locale)
    {
        return (array) include $this->basePath . $locale . '/all.php';
    }

}
