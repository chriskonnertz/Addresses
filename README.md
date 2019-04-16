# Address Validation

> ATTENTION: This repository is no longer maintained!

This PHP class tries to validate user input (name and address).

## Instanciate without auto loading

```php
require __DIR__.'/src/ChrisKonnertz/Addresses/Addresses.php';
require __DIR__.'/src/ChrisKonnertz/Addresses/ResultInterface.php';
require __DIR__.'/src/ChrisKonnertz/Addresses/Result.php';

$addresses = new \ChrisKonnertz\Addresses\Addresses();
```

> If you use auto loading via Composer you can skip the `require` statements.

## Set API key

```php
$addresses->setApiKey('your-api-key');
```

## Set language code

```php
$addresses->setLanguage('de');
```

## Validate data

```php
$result = $addresses->validate();
```

The `validate`method returns an object that implements `ReturnInterface`. Per default this is a `Result` object.

## Check if result (data) is valid

```php
$valid = $result->isValid();
```

If the data is not valid, the `$result` object contains an array of invalid values.
Retrieve this array with `$result->getData()`.

## Check if address has been corrected

The Google Geocache API tries to correct addresses for instance if the street name contains a spelling mistake.
Use these methods to check the address state: `addressFound`, `addressNotFound` and `addressFoundAfterCorrection`

## Example HTML form

```html
<form method="POST" action="">
    <label for="forename">Forename</label>
    <input type="text" id="forename" name="forename">

    <label for="lastname">Last Name</label>
    <input type="text" id="lastname" name="lastname">

    <label for="postalcode">Postal Code</label>
    <input type="text" id="postalcode" name="postalcode">

    <label for="location">Location</label>
    <input type="text" id="location" name="location">

    <label for="street">Street</label>
    <input type="text" id="street" name="street">

    <label for="streetnumber">House Number</label>
    <input type="text" id="streetnumber" name="streetnumber">

    <label for="country">Country</label>
    <input type="text" id="country" name="country">

    <input type="submit" value="Validate" id="submit" name="submit">
</form>`
```

## Example PHP code

```php
if (isset($_POST['submit'])) {
    // Assuming Composer auto loading
    $addresses = new \ChrisKonnertz\Addresses\Addresses();

    $addresses->setAll($_POST);
    
    $result = $addresses->validate();
    
    if ($result->isValid()) {
        echo 'Data is valid.';
    } else {
        echo 'Data is invalid!';
    }
}
```
