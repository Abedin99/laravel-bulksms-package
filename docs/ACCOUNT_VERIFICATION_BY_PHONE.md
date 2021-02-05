## How to Implement Account Verification and Login by Phone in Laravel

### Modifying Our User Model

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
