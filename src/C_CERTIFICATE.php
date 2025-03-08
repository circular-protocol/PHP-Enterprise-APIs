<?php
namespace Circularprotocol\Circularenterpriseapis;
require_once __DIR__ . '/helper.php'; // Ensure this points to the correct location of your helper.php file


use Elliptic\EC;
use DateTime;
use DateTimeZone;
use Exception;

/*******************************************************************************
 * Circular Certificate Class for certificate chaining
 */
class C_CERTIFICATE {
    private $data;
    public $previousTxID;
    public $previousBlock;

    public function __construct() {
        $this->data = null;
        $this->previousTxID = null;
        $this->previousBlock = null;
    }

    /**
     * Insert application data into the certificate
     * @param string $data Data content
     */
    public function setData(string $data): void {
        $this->data = stringToHex($data);
    }

    /**
     * Extract application data from the certificate
     * @return string Data content
     */
    public function getData(): string {
        return hexToString($this->data);
    }

    /**
     * Get the certificate in JSON format
     * @return string JSON-encoded certificate
     */
    public function getJSONCertificate(): string {
        $certificate = [
            "data" => $this->getData(),
            "previousTxID" => $this->previousTxID,
            "previousBlock" => $this->previousBlock,
            "version" => LIB_VERSION
        ];
        return json_encode($certificate);
    }

    /**
     * Get the size of the certificate in bytes
     * @return int Size of the certificate
     */
    public function getCertificateSize(): int {
        return strlen($this->getJSONCertificate());
    }
}

