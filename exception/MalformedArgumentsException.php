<?php
/**
 * @author    Labs64 <netlicensing@labs64.com>
 * @license   Apache-2.0
 * @link      http://netlicensing.io
 * @copyright 2016 Labs64 NetLicensing
 */

namespace NetLicensing;

use Exception;

class MalformedArgumentsException extends Exception
{
    public function __toString()
    {
        return get_class($this) . ": [" . $this->getCode() . "]: " . $this->getMessage() . "\n";
    }
}