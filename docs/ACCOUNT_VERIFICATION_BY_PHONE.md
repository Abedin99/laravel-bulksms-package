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

After running this command, run the migration.

```bash
    php artisan migrate
```

Still, in app/User.php file, let's add a couple of methods to help us in the verification process. Add the following methods:

```php
    public function hasVerifiedPhone()
    {
        return ! is_null($this->phone_verified_at);
    }

    public function markPhoneAsVerified()
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
```

The `hasVerifiedPhone` method just checks to see if the phone number of the user is verified. The `markPhoneAsVerified` verifies the phone number of the user by setting the `phone_verified_at` field of the user to the current timestamp.


### Modify Authentication Controllers

Like I mentioned earlier, we would not want a situation where a user registers but is unable to log in simply because our app expects an email for authentication. Let's make sure that does not happen. Open `app/Http/Controllers/Auth/LoginController.php` and add the following method:


```bash
    public function username()
    {
        $loginType = request()->input('phone');
        $this->phone = filter_var($loginType, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        request()->merge([$this->phone => $loginType]);

        return property_exists($this, 'phone') ? $this->phone : 'email';
    }
```

we edit `app/Http/Controllers/Auth/RegisterController.php` and change the validator and  create methods like so:

```bash
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => ['required', 'string', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $request->email,
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }
```

### Modify Authentication Views

Open the `resources/views/auth/register.blade.php` and edit the portion asking for email to the following:

```html
    <div class="form-group row">
        <label for="phone" class="col-md-4 col-form-label text-md-right">Phone</label>

        <div class="col-md-6">
            <input id="phone" type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" name="phone" value="{{ old('phone') }}" required>

            @if ($errors->has('phone'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('phone') }}</strong>
                </span>
            @endif
        </div>
    </div>
```

Also, open the `resources/views/auth/login.blade.php` and edit the portion asking for email to the following:

```html
    <div class="form-group row">
        <label for="phone" class="col-md-4 col-form-label text-md-right">Phone</label>

        <div class="col-md-6">
            <input id="phone" type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" name="phone" value="{{ old('phone') }}" required autofocus>

            @if ($errors->has('phone'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('phone') }}</strong>
                </span>
            @endif
        </div>
    </div>
```

### Create a Middleware

Laravel makes it very easy to create a middleware. Just switch to your terminal and run the artisan command:

```bash
    php artisan make:middleware EnsurePhoneIsVerified
```

Open the middleware and update the following code to the `handle` method:

```php
    if (! $request->user()->hasVerifiedPhone()) {
        return redirect()->route('phoneverification.notice');
    }

    return $next($request);
```

Now we need to register the middleware so that  the application knows about it. Open the `app/Http/Kernel.php` and add this to the  `$routeMiddleware` array.


```php
    'verifiedphone' => \App\Http\Middleware\EnsurePhoneIsVerified::class,
```

Lets apply the middleware to the home route. Open up `routes/web.php` and edit it like so:

```php
    Route::get('/home', 'HomeController@index')->name('home')->middleware('verifiedphone'); 
```

Now any user that visits the home route without verifying their phone number will be redirected to the route we specified in the middleware.

### Define Necessary Routes

Open `routes/web.php` and add the following code it:

```php
    Route::get('phone/verify', 'PhoneVerificationController@show')->name('phoneverification.notice');
    Route::post('phone/verify', 'PhoneVerificationController@verify')->name('phoneverification.verify');
```

As you can see from the above routes, we are routing traffic to a `PhoneVerificationController` that we don’t have yet. Let's quickly create that using artisan. Run the following command:

```bash
    php artisan make:controller Auth\PhoneVerificationController
```

Open that controller and add the following code to it:

```php
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedPhone()
                        ? redirect()->route('home')
                        : view('verifyphone');
    }

    public function verify(Request $request)
    {
        if (session('verification_code') !== $request->code) {
            throw ValidationException::withMessages([
                'code' => ['The code your provided is wrong. Please try again or request another call.'],
            ]);
        }

        if ($request->user()->hasVerifiedPhone()) {
            return redirect()->route('home');
        }

        $request->user()->markPhoneAsVerified();

        return redirect()->route('home')->with('status', 'Your phone was successfully verified!');
    }
```

Create a file at resources/views/verifyphone.blade.php and add the following code to it:

```html
    @extends('layouts.app')

    @section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">Verify your phone</div>
                    <div class="card-body">
                        <p>Thanks for registering with our platform. We will call you to verify your phone number in a jiffy. Provide the code below.</p>

                        <div class="d-flex justify-content-center">
                            <div class="col-8">
                                <form method="post" action="{{ route('phoneverification.verify') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="code">Verification Code</label>
                                        <input id="code" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" name="code" type="text" placeholder="Verification Code" required autofocus>
                                        @if ($errors->has('code'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-primary">Verify Phone</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
```

We have everything all set up. We now need to hook into the registration flow and call the user. Laravel provides a nice hook for us to do that. `In app/Http/Controllers/Auth/RegisterController.php`, after successful registration, Laravel calls a method `registered`. This means we can override this method to call the user after successful registration. Open `app/Http/Controllers/Auth/RegisterController.php` and add the method:

```php
    protected function registered(Request $request, User $user)
    {
        $user->callToVerify();
        return redirect($this->redirectPath());
    }
```

This is where we actually call the user and disclose the verification code. We first generate a random 6 digit code and store it to the user.
