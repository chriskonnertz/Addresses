<?php
namespace ChrisKonnertz\Addresses;

/**
 * A class (object) that implements this interface represents
 * a result of the validate() method of the Addresses class.
 */
interface ResultInterface
{

    /**
     * @return int
     */
    public function getAddressState();

    /**
     * @param int $addressState
     */
    public function setAddressState($addressState);

    /**
     * @return boolean
     */
    public function isValid();

    /**
     * @param boolean $valid
     */
    public function setValid($valid);

    /**
     * @return array
     */
    public function getData();

    /**
     * @param array $data
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getCorrections();

    /**
     * @param array corrections
     */
    public function setCorrections(array $corrections);

    public function setAddressStateToNotFound();

    public function setAddressStateToFound();

    public function setAddressStateToFoundAfterCorrection();

    /**
     * @return bool
     */
    public function addressFound();

    /**
     * @return bool
     */
    public function addressNotFound();

    /**
     * @return bool
     */
    public function addressFoundAfterCorrection();

}