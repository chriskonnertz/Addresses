<!DOCTYPE html>
<html>
    <head>
        <title>Addresses Test Page</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
        <style>
            body { padding: 20px; }

        </style>
    </head>
    <body>
        <h1>Addresses Test Page</h1>
        <?php

            error_reporting(E_ALL);

            if (isset($_POST['submit'])) {
                require __DIR__.'/src/ChrisKonnertz/Addresses/Addresses.php';
                require __DIR__.'/src/ChrisKonnertz/Addresses/ResultInterface.php';
                require __DIR__.'/src/ChrisKonnertz/Addresses/Result.php';

                $addresses = new \ChrisKonnertz\Addresses\Addresses();

                $key = 'AIzaSyBkzUZQleLpDJ2VcE84ZSg34qWg72w6ltY';
                $addresses->setApiKey($key);
                $addresses->setLanguage('de');

                $resultObject = new \ChrisKonnertz\Addresses\Result;
                //$addresses->injectResultObject($resultObject);

                $addresses->setAll($_POST);

                $customValidator = function ($attribute, $value) {
                    return false;
                };

                //$addresses->setCustomValidator($customValidator);

                $result = $addresses->validate();

                var_dump($result);

                echo '<a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'">Clear</a>';

            } else {
                $result = null;
            }

            function fill_form_field($result, $name)
            {
                if ($result and $result->isValid()) {
                    echo $result->getData()[$name];
                } elseif (isset($_POST[$name])) {
                    echo $_POST[$name];
                }
            }

        ?>

        <form class="pure-form pure-form-stacked" method="POST" action="">
            <label for="forename">Forename</label>
            <input type="text" id="forename" name="forename" value="<?php fill_form_field($result, 'forename') ?>">

            <label for="lastname">Last Name</label>
            <input type="text" id="lastname" name="lastname" value="<?php fill_form_field($result, 'lastname') ?>">

            <label for="postalcode">Postal Code</label>
            <input type="text" id="postalcode" name="postalcode" value="<?php fill_form_field($result, 'postalcode') ?>">

            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?php fill_form_field($result, 'location') ?>">

            <label for="street">Street</label>
            <input type="text" id="street" name="street" value="<?php fill_form_field($result, 'street') ?>">

            <label for="streetnumber">House Number</label>
            <input type="text" id="streetnumber" name="streetnumber" value="<?php fill_form_field($result, 'streetnumber') ?>">

            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="<?php fill_form_field($result, 'country') ?>">

            <input class="pure-button pure-button-primary" type="submit" value="Validate" id="submit"
                   name="submit">
        </form>
    </body>
</html>