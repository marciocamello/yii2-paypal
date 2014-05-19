PayPal extension for the Yii2
===========

PayPal payment extension for the Yii2.

Installation
====

Add to the composer.json file following section:

```
"andrey-klimenko/yii2-paypal": "dev-master"
```

Add to to you Yii2 config file this part with component settings:

```
'paypal'       => [
            'class'        => 'ak\Paypal',
            'clientId'     => 'you_client_id',
            'clientSecret' => 'you_client_secret',
            'isProduction' => false,
            // This is config file for the PayPal system
            'config'       => [
                'http.ConnectionTimeOut' => 30,
                'http.Retry'             => 1,
                'mode'                   => \ak\Paypal::MODE_SANDBOX, // development (sandbox) or production (live) mode
                'log.LogEnabled'         => YII_DEBUG ? 1 : 0,
                'log.FileName'           => '@runtime/logs/paypal.log',
                'log.LogLevel'           => \ak\Paypal::LOG_LEVEL_FINE,
            ]
        ],
```
