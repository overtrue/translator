Translator
==========

PHP多语言支持工具

## Usage
 1. 目录结构举例：

```shell
 app/
 |-- i18n/
 |    |-- zh_CN/
 |    |    |-- all.php   # return array('key' => 'pattern');
 |    |-- en_US/
 ...
```

 2. 在字符串中使用变量:

```php
 //app/i18n/en_US/all.php
 <?php
 return array(
    // key => pattern
    'user_not_exists' => 'use {name} not exists.',
    ...
 );
 ```

 3. 使用Translator:

```php
 <?php

 use Overtrue\Translator;

 $translator = new Translator($appPath . '/i18n', 'zh_CN');//new Translator(语言包目录, 当前语言名)

 //格式化语言包里的key
 $username = 'overtrue';
 echo $translator->trans('user_not_exists.', ['name' => $username]);
 // output: 'use overtrue not exists.'

 //格式化指定的字符串：
 echo $translator->format('user {name} not exists.', ['name' => $username]);
 // output: 'use overtrue not exists.'

```

##License

MIT
