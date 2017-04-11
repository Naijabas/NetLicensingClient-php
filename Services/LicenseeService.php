<?php
/**
 * @author    Labs64 <netlicensing@labs64.com>
 * @license   Apache-2.0
 * @link      http://netlicensing.io
 * @copyright 2016 Labs64 NetLicensing
 */
namespace NetLicensing;

class LicenseeService extends BaseEntityService
{
    const SERVICE_URL = '/licensee';
    const LICENSEE_ENDPOINT_PATH_VALIDATE = 'validate';
    const LICENSEE_ENDPOINT_PATH_TRANSFER = 'transfer';

    public static function connect(NetLicensingAPI $nlic_connect)
    {
        return new LicenseeService($nlic_connect);
    }

    public function getList()
    {
        return $this->_list($this->nlic_connect);
    }

    public function get($number)
    {
        return $this->_get($number, $this->nlic_connect);
    }

    public function create(Licensee $licensee)
    {
        return $this->_create($licensee, $this->nlic_connect);
    }

    public function update(Licensee $licensee)
    {
        return $this->_update($licensee, $this->nlic_connect);
    }

    public function delete($number, $force_cascade = FALSE)
    {
        return $this->_delete($number, $this->nlic_connect, $force_cascade);
    }

    public function validate($licensee_number, $product_number = '', $license_name = '')
    {
        $params = array();
        $licensee_number = (string)$licensee_number;

        if (empty($licensee_number)) {
            throw new NetLicensingException('Licensee Number cannot be empty');
        }

        //check product number(s)
        if (!empty($product_number)) {
            switch (gettype($product_number)) {
                case 'string':
                    $params['productNumber'] = $product_number;
                    break;
                case 'array':
                    $count = count($product_number);
                    $index = ($count > 1) ? 0 : '';
                    foreach ($product_number as $number) {
                        switch (gettype($number)) {
                            case 'int':
                                $params['productNumber' . $index] = (string)$number;
                                break;
                            case 'string':
                                $params['productNumber' . $index] = $number;
                                break;
                            case 'object':
                                if ($number instanceof Product) {
                                    if (!$number->getOldProperty('number')) {
                                        throw new NetLicensingException('Validation error: product number cannot be empty');
                                    }

                                    $params['productNumber' . $index] = $number->getOldProperty('number');
                                } else {
                                    throw new NetLicensingException('Validation error: entity ' . get_class($number) . ' is invalid; must be instanceof Product');
                                }
                                break;
                            default:
                                throw new NetLicensingException('Validation error: product number cannot be ' . gettype($product_number));
                                break;
                        }
                        if ($count > 1) $index++;
                    }
                    break;
                default:
                    if (!is_string($license_name)) {
                        throw new NetLicensingException('Validation error: wrong product number type provided ' . gettype($product_number));
                    }
                    break;
            }
        }

        if ($license_name) {
            if (!is_string($license_name)) {
                throw new NetLicensingException('Validation error: license name is not string ' . gettype($product_number));
            }
            $params['licenseeName'] = $license_name;
        }

        $response = $this->nlic_connect->post($this->_getServiceRequestUrl() . '/' . $licensee_number . '/' . self::LICENSEE_ENDPOINT_PATH_VALIDATE, $params);

        return NetLicensingAPI::getPropertiesByXml($response);
    }

    /**
     * Transfer licenses between licensees.
     * TODO(AY): Wiki Link
     *
     * @param $licensee_number
     * @param $sourceLicenseeNumber
     * @return boolean
     * @throws NetLicensingException
     */
    public function transfer($licensee_number, $sourceLicenseeNumber)
    {
        $params = array();
        $licensee_number = (string)$licensee_number;

        if (empty($licensee_number)) {
            throw new NetLicensingException('Licensee Number cannot be empty');
        }
        if (empty($sourceLicenseeNumber)) {
            throw new NetLicensingException('Source Licensee Number cannot be empty');
        }

        if (!is_string($sourceLicenseeNumber)) {
            throw new NetLicensingException('Transfer error: Source Licensee Number is not string ' . gettype($sourceLicenseeNumber));
        }
        $params['sourceLicenseeNumber'] = $sourceLicenseeNumber;

        $this->nlic_connect->post($this->_getServiceRequestUrl() . '/' . $licensee_number . '/' . self::LICENSEE_ENDPOINT_PATH_TRANSFER, $params);

        $status_code = $this->nlic_connect->getHttpStatusCode();
        return (!empty($status_code) && $status_code == '204') ? TRUE : FALSE;
    }
    public static function validateByApiKey($api_key)
    {
        // TODO
    }

    protected function _createEntity()
    {
        return new Licensee();
    }

    protected function _getServiceUrl()
    {
        return self::SERVICE_URL;
    }
} 
