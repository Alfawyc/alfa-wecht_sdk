# wechat_sdk
<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

## Wechat SDK For Larevel


- install
```bash
composer require alfa/wechat-sdk
```

- Add the following class to the `providers` array in `config/app.php`:
```php
\Alfa\Wechat\src\WechatServiceProvider::class
```

- publish config
```bash
php artisan vendor:publish --tag=alfa-wechat-config
```

- modify `config/wechat.php` replace you appId and appSecret