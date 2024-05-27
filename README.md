# Make API response easy

## Install

```
composer require bpartner/api-response
```

## Publish config file

```
php artisan vendor:publish --tag=api-response-config
```

## Use

### In controller 

```php
return API::success($data);
return API::error();
return API::notFound();
return API::ok();
```

