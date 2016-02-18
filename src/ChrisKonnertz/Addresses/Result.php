<?php
namespace ChrisKonnertz\Addresses;

/**
 * This class (object) represents a result of the validate() method of the Addresses class.
 */
class Result implements ResultInterface
{

    const ADDRESS_NOT_FOUND = 0;

    const ADDRESS_FOUND = 1;

    const ADDRESS_FOUND_AFTER_CORRECTION = 2;

    /**
     * Is the data valid?
     * @var bool
     */
    protected $valid = false;

    /**
     * Address found, not found or corrected?
     * @var int
     */
    protected $addressState = self::ADDRESS_NOT_FOUND;

    /**
     * Array with the data
     * @var array
     */
    protected $data = [];

    /**
     * Array with name of corrected address parts
     * @var array
     */
    protected $corrections = [];

    /**
     * @param bool $valid Is the data valid?
     * @param int $addressState Address found, not found or corrected? See constants
     * @param array $data Array with the data
     * @param array $corrections Array with name of corrected address parts
     */
    function __construct($valid = false, $addressState = self::ADDRESS_NOT_FOUND, array $data = [],
                         array $corrections = [])
    {
        $this->valid = $valid;
        $this->addressState = $addressState;
        $this->data = $data;
        $this->corrections = $corrections;
    }

    /**
     * @return int
     */
    public function getAddressState()
    {
        return $this->addressState;
    }

    /**
     * @param int $addressState
     */
    public function setAddressState($addressState)
    {
        $this->addressState = $addressState;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @param boolean $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getCorrections()
    {
        return $this->corrections;
    }

    /**
     * @param array $corrections
     */
    public function setCorrections(array $corrections)
    {
        $this->corrections = $corrections;
    }

    /**
     * Set address state to "not found"
     */
    public function setAddressStateToNotFound()
    {
        $this->setAddressState(self::ADDRESS_NOT_FOUND);
    }

    /**
     * Set address state to "found"
     */
    public function setAddressStateToFound()
    {
        $this->setAddressState(self::ADDRESS_FOUND);
    }

    /**
     * Set address state to "found after correction"
     */
    public function setAddressStateToFoundAfterCorrection()
    {
        $this->setAddressState(self::ADDRESS_FOUND_AFTER_CORRECTION);
    }

    /**
     * Checks if the address has been found.
     * @return bool
     */
    public function addressFound()
    {
        return $this->addressState === self::ADDRESS_FOUND;
    }

    /**
     * Checks if the address has not been found.
     * @return bool
     */
    public function addressNotFound()
    {
        return $this->addressState === self::ADDRESS_NOT_FOUND;
    }

    /**
     * Checks if the address has been found after a correction.
     * @return bool
     */
    public function addressFoundAfterCorrection()
    {
        return $this->addressState === self::ADDRESS_FOUND_AFTER_CORRECTION;
    }

}