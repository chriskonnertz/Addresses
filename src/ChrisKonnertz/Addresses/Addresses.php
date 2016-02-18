<?php
namespace ChrisKonnertz\Addresses;

use Exception;
use Closure;

class Addresses
{

    /**
     * Set to true to enable a plausibility checks
     * NOTE: These check is not reliable!
     */
    const PLAUSIBILITY_CHECK = true;

    /**
     * Google Geocode API URL
     */
    const API_URL = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

    /**
     * The Google Geocode API key. It's not necessary to use one but you should!
     * @var string
     */
    protected $apiKey = '';

    /**
     * The language code. The Google Geocode API will try to translate country and cities names.
     * Examples: 'en', 'de', ...
     * @var string
     */
    protected $language = 'en';

    /**
     * The fore name
     * @var string
     */
    protected $forename = null;

    /**
     * The lastname
     * @var string
     */
    protected $lastname = null;

    /**
     * The postal code
     * NOTE: No camel case here
     * @var string
     */
    protected $postalcode = null;

    /**
     * The name of the location (town)
     * @var string
     */
    protected $location = null;

    /**
     * The name of the street
     * @var string
     */
    protected $street = null;

    /**
     * The street number (house number)
     * NOTE: No camel case here
     * @var string
     */
    protected $streetnumber = null;

    /**
     * The name of the country
     * @var string
     */
    protected $country = null;

    /**
     * A custom validator closure (see setter for more details)
     * @var Closure
     */
    protected $customValidator = null;

    /**
     * The result object
     * @var ResultInterface
     */
    protected $result = null;

    /**
     * Array with the name of all attributes
     * @var array
     */
    protected static $validAttributes = [
        'forename',
        'lastname',
        'postalcode',
        'location',
        'street',
        'streetnumber',
        'country'
    ];

    /**
     * @return array $dataArray Array with the data (key = attribute name)
     */
    function __construct(array $dataArray = array())
    {
        if (sizeof($dataArray) > 0) {
            $this->setAll($dataArray);
        }
    }

    /**
     * Dependency injection: Inject your own result object (that implements ResultInterface)
     * if you do not want to use the default Result class.
     * @param $resultObject ResultInterface
     * @throws Exception
     */
    public function injectResultObject($resultObject)
    {
        $interfaceName = 'ChrisKonnertz\Addresses\ResultInterface';
        if ($resultObject !== null and is_object($resultObject) and is_a($resultObject, $interfaceName)) {
            $this->result = $resultObject;
        } else {
            throw new Exception('Error: Invalid result object.');
        }
    }

    /**
     * Validates the data. Returns an object that implements the ResultInterface.
     * @return ResultInterface
     */
    public function validate()
    {
        $valid = true;
        $addressState = null;
        $resultArray = [];

        // Create new result object if none has been set (via injectResultObject())
        if ($this->result === null) {
            $this->result = new Result();
        }

        // Check if all attributes have been set
        foreach (self::$validAttributes as $attribute) {
            $attributeValid = true;

            // Remove unnecessary spaces
            // IDEA: Remove double (triple...) spaces (with regex?)
            $this->$attribute = trim($this->$attribute);

            // Check for === null and === ''
            if ($this->$attribute == null) {
                $attributeValid = false;
            }

            // Call custom validator closure (if one is set)
            if ($this->customValidator !== null) {
                $customValidator = $this->customValidator;
                $validatorResult = $customValidator($attribute, $this->$attribute);

                if (!$validatorResult) {
                    $attributeValid = false; // (We have no guarantee that $validatorResult contains false)
                }
            }

            if (! $attributeValid) {
                $valid = false;
                $resultArray[$attribute] = $this->$attribute;
            }
        }

        // Specific checks
        if (! is_numeric($this->postalcode)) {
            $valid = false;
            $resultArray['postalcode'] = $this->postalcode;
        }
        if (! is_numeric($this->streetnumber)) {
            $valid = false;
            $resultArray['streetnumber'] = $this->streetnumber;
        }

        // Only check address if all values are set (so we know there exists data).
        if (sizeof($resultArray) == 0) {
            $address = $this->postalcode.' '.$this->location
                .', '.$this->street.' '.$this->streetnumber
                .', '.$this->country;

            $geoResult = $this->validateAddressViaGeocode($address);

            if ($geoResult === false) {
                $valid = false;
                $this->result->setAddressStateToNotFound();
            } else {
                $this->result->setAddressStateToFound();

                $originalPostalcode = $this->postalcode;
                $originalLocation = $this->location;

                $corrections = [];

                // Corrections of address parts (if they differ from the original address)
                if (isset($geoResult['streetnumber']) and $geoResult['streetnumber'] != $this->streetnumber) {
                    $corrections[] = 'streetnumber';
                    $this->streetnumber = $geoResult['streetnumber'];
                }
                if (isset($geoResult['street']) and $geoResult['street'] != $this->street) {
                    $corrections[] = 'street';
                    $this->street = $geoResult['street'];
                }
                if (isset($geoResult['location']) and $geoResult['location'] != $this->location) {
                    $corrections[] = 'location';
                    $this->location = $geoResult['location'];
                }
                if (isset($geoResult['country']) and $geoResult['country'] != $this->country) {
                    $corrections[] = 'country';
                    $this->country = $geoResult['country'];
                }
                if (isset($geoResult['postalcode']) and $geoResult['postalcode'] != $this->postalcode) {
                    $corrections[] = 'postalcode';
                    $this->postalcode = $geoResult['postalcode'];
                }

                if (sizeof($corrections) > 0) {
                    $this->result->setCorrections($corrections);
                    $this->result->setAddressStateToFoundAfterCorrection();
                }

                // Check if it's only a partial match
                if (! $geoResult['foundFullAddress']) {
                    $valid = false;
                    $this->result->setAddressStateToNotFound();
                }

                if (self::PLAUSIBILITY_CHECK) {
                    // Plausibility check: If city has been changed but postal code is still the same,
                    // they do not seem to match. (However it still might just have been a typo)
                    if ($originalPostalcode == $this->postalcode and $originalLocation !== $this->location) {
                        $valid = false;
                        $resultArray['postalcode'] = $this->postalcode;
                        $resultArray['location'] = $this->location;
                        // IDEA: Maybe set address state to "not found"? (However the API actually found something.)
                    }
                }

            }
        }

        // Return all data if all values are valid
        if ($valid) {
            $resultArray = $this->getAll();
        }

        $this->result->setValid($valid);
        $this->result->setData($resultArray);

        return $this->result;
    }

    /**
     * @param $address
     * @return bool
     * @throws Exception
     */
    public function validateAddressViaGeocode($address)
    {
        $curl = curl_init();

        $url = self::API_URL.urlencode($address).'&language='.$this->language;

        if ($this->apiKey) {
            $url .= '&key='.$this->apiKey;
        }

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // Do not try to verify the SSL certificate - it could fail
        curl_setopt($curl, CURLOPT_URL, $url);

        $result = curl_exec($curl);

        // This means the curl request failed (server did not respond?)
        if ($result === false) {
            throw new Exception('Error: API request failed.');
        }

        $data = json_decode($result);

        if (! property_exists($data, 'results') or ! is_array($data->results) or sizeof($data->results) == 0) {
            return false;
        }

        $resultArray = [];

        // Work with the first result item even if there are more than just one
        $addressItem = $data->results[0];

        // Street number, etc.
        $addressComponents = $addressItem->address_components;

        foreach ($addressComponents as $addressComponent) {
            if (in_array('street_number', $addressComponent->types)) {
                $resultArray['streetnumber'] = $addressComponent->long_name;
            }

            // Street
            if (in_array('route', $addressComponent->types)) {
                $resultArray['street'] = $addressComponent->long_name;
            }

            // Location
            if (in_array('locality', $addressComponent->types)) {
                $resultArray['location'] = $addressComponent->long_name;
            }

            if (in_array('country', $addressComponent->types)) {
                $resultArray['country'] = $addressComponent->long_name;
            }

            if (in_array('postal_code', $addressComponent->types)) {
                $resultArray['postalcode'] = $addressComponent->long_name;
            }
        }

        // This attribute only exists if the address has been found
        $resultArray['foundFullAddress'] = property_exists($addressItem, 'formatted_address');

        return $resultArray;
    }

    /**
     * This method allows you to set a custom validator.
     * The validator is a closure that will receive two parameters:
     * $attribute (string, name of the attribute, for example "forename")
     * $value (string, value of the attribute)
     * The return value has to be a boolean: True = valid, false = invalid.
     * The validator closure will be called for all attributes.
     * NOTE: The validator can mark attributes as invalid but not as valid! (black list)
     * @param Closure $customValidator
     * @throws Exception
     */
    public function setCustomValidator(Closure $customValidator)
    {
        // IDEA: Maybe there should be more checks for example if the
        // closure accepts two parameters.
        if ($customValidator !== null and $customValidator instanceof Closure) {
            $this->customValidator = $customValidator;
        } else {
            throw new Exception('Error: Invalid custom validator.');
        }
    }

    /**
     * Get all attributes and their values
     *
     * @return array $dataArray Array with the data (key = attribute name)
     */
    public function getAll()
    {
        $valueArray = [];

        foreach(self::$validAttributes as $attribute) {
            $valueArray[$attribute] = $this->$attribute;
        }

        return $valueArray;
    }

    /**
     * Set all attributes and their values
     *
     * @param array $dataArray Array with the data (key = attribute name)
     * @return void
     */
    public function setAll(array $dataArray)
    {
        foreach ($dataArray as $attribute => $value) {
            $this->setAttribute($dataArray, $attribute);
        }
    }

    /**
     * Set the value of an attribute (by data array and attribute name) - if possible
     * @param array $dataArray Array with the data (key = attribute name)
     * @param string $attribute The name of the attribute
     * @return void
     */
    protected function setAttribute($dataArray, $attribute)
    {
        if (isset($dataArray[$attribute])) {
            if (in_array($attribute, self::$validAttributes)) {
                $this->$attribute = $dataArray[$attribute];
            }
        }
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->apiKey;
    }

    /**
     * @param string language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getForename()
    {
        return $this->forename;
    }

    /**
     * @param string $forename
     */
    public function setForename($forename)
    {
        $this->forename = $forename;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getPostalcode()
    {
        return $this->postalcode;
    }

    /**
     * @param string $postalcode
     */
    public function setPostalcode($postalcode)
    {
        $this->zip = $postalcode;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getStreetnumber()
    {
        return $this->streetnumber;
    }

    /**
     * @param string streetnumber
     */
    public function setStreetnumber($streetnumber)
    {
        $this->housenumber = $streetnumber;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

}