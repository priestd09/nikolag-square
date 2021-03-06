nikolag/square 
[![Build Status](https://travis-ci.org/NikolaGavric94/nikolag-square.svg?branch=master)](https://travis-ci.org/NikolaGavric94/nikolag-square)
[![Latest Stable Version](https://poser.pugx.org/nikolag/square/v/stable)](https://packagist.org/packages/nikolag/square) 
[![Total Downloads](https://poser.pugx.org/nikolag/square/downloads)](https://packagist.org/packages/nikolag/square) 
[![License](https://poser.pugx.org/nikolag/square/license)](https://packagist.org/packages/nikolag/square) 
=========
Square integration with Laravel 5.5.x built on [nikolag/core](https://github.com/NikolaGavric94/nikolag-core/)

## Installation guide
`composer require nikolag/square --dev`

##### Due to Laravel [Package Discovery](https://laravel.com/docs/5.5/packages#package-discovery), registering service providers and facades manually for this project as of Laravel 5.5 is deprecated and no longer required since the package is adapted to automatically register these stuff for you.
But there are still couple of steps to do in order to use this package.

---

Configuration files will automatically be published for you and you should check it out at `config/nikolag.php` before continuing.

**If for some reason you can't see `square` driver inside of `connections` array, you'll have to add it manually. You can find configuration file here under [src/config/nikolag.php](https://github.com/NikolaGavric94/nikolag-square/blob/master/src/config/nikolag.php) and copy everything from inside `connections` array and append to your `connections` array inside of your `app/config/nikolag.php` file.**

After changing the configuration files or not, you should run migrations with the following command
```javascript
php artisan migrate
```

---

Then add your credentials for Square API inside of `.env` and also add fully qualified name (namespace) for your classes.
```javascript
SQUARE_APPLICATION_ID=<YOUR_APPLICATION_ID>
SQUARE_TOKEN=<YOUR_ACCESS_TOKEN>

SQUARE_USER_NAMESPACE=<USER_NAMESPACE>
SQUARE_ORDER_NAMESPACE=<ORDER_NAMESPACE>
```

To be able to utilize the customers system for Users, your User class must use HasCustomers trait.
```javascript
<?php
...
use Nikolag\Square\Traits\HasCustomers;

class User extends Model {
  use HasCustomers;
  ...
}
```

## Examples
#### Simple usages
```javascript
public function charge() {
  //$amount is in USD currency and is in cents. ($amount = 200 == 2 Dollars)
  $amount = 5000;
  //nonce reference => https://docs.connect.squareup.com/articles/adding-payment-form
  $formNonce = 'some nonce';
  //$location_id is id of a location from Square
  $location_id = 'some location id';
  Square::charge($amount, $formNonce, $location_id);
  //available currencies => https://docs.connect.squareup.com/api/connect/v2/?q=currency#type-currency
  $currency = 'RSD';
  //Default currency is USD, if u wish to change that
  //pass currency as a fourth parameter
  //IMPORTANT NOTE: Your location might not allow some currencies
  //example: when your location is in USA you can't use RSD currency
  //This is a restriction from Square.
  $options = [
    'amount' => $amount,
    'card_nonce' => $formNonce,
    'location_id' => $locationd_id,
    'currency' => $currency
  ];
  Square::charge($options);

  $customer = array(
    'first_name' => $request->first_name,
    'last_name' => $request->last_name,
    'company_name' => $request->company_name,
    'nickname' => $request->nickname,
    'email' => $request->email,
    'phone' => $request->phone,
    'note' => $request->note,
  );
  //or
  $customer = $merchant->hasCustomer($request->email);

  Square::setMerchant($merchant)->setCustomer($customer)->charge($options);
  //or with currency other than USD
  Square::setMerchant($merchant)->setCustomer($customer)->charge($options);
}
```

#### Retrieve all customers for a merchant
```javascript
$merchant->customers;
```

#### Retrieve a customer by email
```javascript
$merchant->hasCustomer('tester@gmail.com');
```

#### Retrieve all transactions for a merchant
```javascript
$merchant->transactions;
```

#### Retrieve all transactions by status
```javascript
//Transactions that passed
$merchant->passedTransactions;
//Transactions that failed
$merchant->failedTransactions;
//Transactions that are pending
$merchant->openedTransactions;
```

#### Charge customers with merchant as a seller
Charging a customer that doesn't exist and connecting it with a merchant and a transaction.
```javascript
public function charge(Request $request) {
  //$amount is in USD currency and is in cents. ($amount = 200 == 2 Dollars)
  $amount = 5000;
  //nonce reference => https://docs.connect.squareup.com/articles/adding-payment-form
  $formNonce = 'some nonce';
  //$location_id is id of a location from Square
  $location_id = 'some location id';
  $customer = array(
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'company_name' => $request->company_name,
      'nickname' => $request->nickname,
      'email' => $request->email,
      'phone' => $request->phone,
      'note' => $request->note
  );

  $merchant->charge($amount, $formNonce, $location_id, $customer);
}
```
Charging already existing customer and connecting both transaction and merchant with it
```javascript
public function charge(Request $request) {
  //$amount is in USD currency and is in cents. ($amount = 200 == 2 Dollars)
  $amount = 5000;
  //nonce reference => https://docs.connect.squareup.com/articles/adding-payment-form
  $formNonce = 'some nonce';
  //$location_id is id of a location from Square
  $location_id = 'some location id';
  $customer = $merchant->hasCustomer($request->email);
  if(!$customer) $customer = null;

  $merchant->charge($amount, $formNonce, $location_id, $customer);
}
```
Charging a customer without saving the customer, but connecting the transaction with the merchant.
```javascript
public function charge(Request $request) {
  //$amount is in USD currency and is in cents. ($amount = 200 == 2 Dollars)
  $amount = 5000;
  //nonce reference => https://docs.connect.squareup.com/articles/adding-payment-form
  $formNonce = 'some nonce';
  //$location_id is id of a location from Square
  $location_id = 'some location id';

  $merchant->charge($amount, $formNonce, $location_id);
}
```

## All available methods
### Trait
```javascript
/**
 * Retrieve merchant customers.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function customers() {}

/**
 * Retrieve customer if he exists, otherwise return false.
 * 
 * @param string $email 
 * @return \Nikolag\Square\Model\Customer|false
 */
public function hasCustomer(string $email) {}

/**
 * All transactions.
 * 
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function transactions() {}

/**
 * Paid transactions.
 * 
 * @return \Illuminate\Database\Eloquent\Collection
 */
public function passedTransactions() {}

/**
 * Pending transactions.
 * 
 * @return \Illuminate\Database\Eloquent\Collection
 */
public function openedTransactions() {}

/**
 * Failed transactions.
 * 
 * @return \Illuminate\Database\Eloquent\Collection
 */
public function failedTransactions() {}

/**
 * Charge a customer.
 * 
 * @param float $amount 
 * @param string $nonce 
 * @param string $location_id 
 * @param mixed $customer 
 * @param string $currency 
 * @return \Nikolag\Square\Models\Transaction
 */
public function charge(float $amount, string $nonce, string $location_id, $customer = null, string $currency = "USD") {}

/**
 * Save a customer.
 * 
 * @param array $customer 
 * @return void
 */
public function saveCustomer(array $customer) {}
```
### Facade
```javascript
/**
 * Charge a customer.
 * 
 * @param array $options 
 * @return \Nikolag\Square\Models\Transaction
 * @throws \Nikolag\Square\Exception on non-2xx response
 */
public function charge(array $options) {}

/**
 * Transactions directly from Square API.
 * 
 * @param array $options 
 * @return \SquareConnect\Model\ListLocationsResponse
 * @throws \Nikolag\Square\Exception on non-2xx response
 */
public function transactions(array $options) {}

/**
 * Getter for customer.
 * 
 * @return mixed
 */
public function getCustomer() {}

/**
 * Setter for customer.
 * 
 * @param mixed $customer 
 * @return void
 */
public function setCustomer($customer) {}

/**
 * Getter for customer.
 * 
 * @return mixed
 */
public function getMerchant() {}

/**
 * Setter for merchant.
 * 
 * @param mixed $merchant 
 * @return mixed
 */
public function setMerchant($merchant) {}
```

## Contributing
Everyone is welcome to contribute to this repository, simply open up an issue
and label the request, whether it is an issue, bug or a feature. For any other
enquiries send an email to nikola.gavric94@gmail.com

### Contributors
| Name                                               | Changes                                                                                                                       | Date       |
| -------------------------------------------------- |:-----------------------------------------------------------------------------------------------------------------------------:|:----------:|
| [@Godlikehobbit](https://github.com/Godlikehobbit) | Add optional currency parameter to charge function [pull request #6](https://github.com/NikolaGavric94/laravel-square/pull/6) | 2017-09-12 |

## License
MIT License

Copyright (c) 2017

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
