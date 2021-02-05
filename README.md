## About Package

At times, you might want to create an app that uses a phone number/password pair as a means of authentication, as opposed to the normal email/password pair. In some other cases, you are not necessarily using phone numbers as a means of authentication, but having a phone number is critical to your app. In such situations, it is very important you verify that the phone numbers your users provide are valid and functional. One way to do this is to give them a call and tell them a code that they will have to provide to your app. If you use Gmail, then you are probably familiar with the voice call verification it uses. In this package, we will be showing you how to achieve that using Laravel and [bartapathao.com](http://bartapathao.com) Bulksmsbd Service excellent service. Let’s get to it.

- [http://sms.positiveitsolution.com](http://sms.positiveitsolution.com).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Installation

This package can be used with Laravel 5.8 or higher.

1. From your projects root folder in terminal run:

```bash
    composer require abedin99/bulksms
```
2. You should publish the migration and the config/bulksms.php config file with:

```php
    'providers' => [
	    Abedin99\Bulksms\BulksmsServiceProvider::class,
	];
```

3. Creating a blogpackage.php file in the /config directory of the Laravel project in which the package was required.

    php artisan vendor:publish --provider="Abedin99\Bulksms\BulksmsServiceProvider" --tag="config"


4. The next thing we are going to do is to add our bulksms credentials to the .env file.

```bash
    BULKSMS_URL=<url>

    BULKSMSBD_USERNAME=<username>

    BULKSMSBD_PASSWORD=<password>
```

## Usage

```php
    use Abedin99\Bulksms\Bulksms;

    Route::get('/bulksms', function() {
        $code = random_int(100000, 999999);

        $number = "+880XXXXXXXXXX"; // with country and area code

        $txt = "Hi, thanks for Joining. This is your verification code: {$code}";

        $send = Bulksms::send('+8801611516222', $txt);

        return $send;
    });
```

## Modifying Our User Model

Now that we have everything set up, we need to start tweaking our app to accept a phone number upon registration instead of email. First, we will change the users table migration file generated for us, when we ran the `php artisan make:auth` command like so:

```php
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('phone')->unique();
        $table->timestamp('phone_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });
```

We also need to make some changes to the app/User.php model. The following code ensures that the phone, name, and password fields are mass assignable. You can read more about mass assignment in the Laravel documentation:

    
```php
    protected $fillable = [
        'phone',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
    ];
```
