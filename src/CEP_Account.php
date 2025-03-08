<?php
namespace Circularprotocol\Circularenterpriseapis;
require_once __DIR__ . '/helper.php'; // Ensure this points to the correct location of your helper.php file


use Elliptic\EC;
use DateTime;
use DateTimeZone;
use Exception;

/*******************************************************************************
 * Circular Account Class
 */
class CEP_Account {
    public $address;
    public $publicKey;
    public $info;
    public $codeVersion;
    public $lastError;
    public $NAG_URL;
    public $NETWORK_NODE;
    public $blockchain;
    public $LatestTxID;
    public $Nonce;
    public $data;
    public $intervalSec;

    public function __construct() {
        $this->address = null;
        $this->publicKey = null;
        $this->info = null;
        $this->codeVersion = LIB_VERSION;
        $this->lastError = '';
        $this->NAG_URL = DEFAULT_NAG;
        $this->NETWORK_NODE = '';
        $this->blockchain = DEFAULT_CHAIN;
        $this->LatestTxID = '';
        $this->Nonce = 0;
        $this->data = [];
        $this->intervalSec = 2;
    }

    /**
     * Open an account by retrieving all the account info
     * @param string $address Account address
     * @return bool True if successful, false otherwise
     */
    public function open(string $address): bool {
        if (empty($address)) {
            $this->lastError = "Invalid address";
            return false;
        }
        $this->address = $address;
        return true;
    }

    /**
     * Update the account data and Nonce field
     * @return bool True if successful, false otherwise
     */
    public function updateAccount(): bool {
        if (empty($this->address)) {
            $this->lastError = "Account not open";
            return false;
        }

        $data = [
            "Blockchain" => hexFix($this->blockchain),
            "Address" => hexFix($this->address),
            "Version" => $this->codeVersion,
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);

        try {
            $response = file_get_contents($this->NAG_URL."Circular_GetWalletNonce_", false, $context);

            if ($response === false) {
                throw new Exception("Network error: " . error_get_last()['message']);
            }

            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }

            if ($responseData['Result'] == 200 && isset($responseData['Response']['Nonce'])) {
                $this->Nonce = $responseData['Response']['Nonce'] + 1;
                return true;
            } else {
                $this->lastError = "Invalid response format or missing Nonce field";
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = "Network error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Set the blockchain network
     * @param string $network Network name (e.g., 'devnet', 'testnet', 'mainnet')
     * @return string URL of the network
     * @throws Exception If network URL cannot be fetched
     */
    public function setNetwork(string $network): string {
        $nagUrl = NETWORK_URL . urlencode($network);

        try {
            $response = file_get_contents($nagUrl);
            if ($response === false) {
                throw new Exception("Failed to fetch URL: " . error_get_last()['message']);
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse JSON: " . json_last_error_msg());
            }

            if (isset($data['status']) && $data['status'] === 'success' && isset($data['url'])) {
                return $data['url'];
            } else {
                throw new Exception($data['message'] ?? 'Failed to get URL');
            }
        } catch (Exception $e) {
            error_log('Error fetching network URL: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Set the blockchain address
     * @param string $blockchain Blockchain address
     */
    public function setBlockchain(string $blockchain): void {
        $this->blockchain = $blockchain;
    }

    /**
     * Close the account
     */
    public function close(): void {
        $this->address = null;
        $this->publicKey = null;
        $this->info = null;
        $this->lastError = '';
        $this->NAG_URL = null;
        $this->NETWORK_NODE = null;
        $this->blockchain = null;
        $this->LatestTxID = null;
        $this->data = null;
        $this->Nonce = 0;
        $this->intervalSec = 0;
    }

    /**
     * Sign data using the account's private key
     * @param string $data Data to sign
     * @param string $privateKey Private key
     * @param string $address Account address
     * @return string Signature
     * @throws Exception If account is not open
     */
    public function signData(string $data, string $privateKey, string $address): string {
        if (empty($this->address)) {
            throw new Exception("Account is not open");
        }

        $ec = new EC('secp256k1');
        $key = $ec->keyFromPrivate(hexFix($privateKey), 'hex');
        $msgHash = hash('sha256', $data);

        $signature = $key->sign($msgHash)->toDER('hex');
        return $signature;
    }


    /** 
     * Get th 
    */
    public function getTransactionbyID(string $TxID, int $Start, int $End): array {
        // Prepare the data for the request
        $data = [
            "Blockchain" => hexFix($this->blockchain),
            "ID" => hexFix($TxID),
            "Start" => strval($Start),
            "End" => strval($End),
            "Version" => $this->codeVersion
        ];
    
        // Prepare the URL
        $url = $this->NAG_URL . 'Circular_GetTransactionbyID_' . $this->NETWORK_NODE;
    
        // Use cURL to send the request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        // Execute the request
        $response = curl_exec($ch);

    
        // Check for cURL errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: " . $error);
        }
    
        // Get the HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        // Check if the response is successful
        if ($httpCode !== 200) {
            throw new Exception("Network response was not ok. HTTP Code: " . $httpCode);
        }
    
        // Decode the JSON response
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
    
        return $responseData;
    }

    /**
     * Search for a transaction by its ID
     * @param int $blockNum Block number
     * @param string $txID Transaction ID
     * @return array Transaction data
     * @throws Exception If network request fails
     */
    public function getTransaction(int $blockNum, string $txID): array {
        $data = [
            "Blockchain" => hexFix($this->blockchain),
            "ID" => hexFix($txID),
            "Start" => strval($blockNum),
            "End" => strval($blockNum),
            "Version" => $this->codeVersion
        ];

        $url = $this->NAG_URL . 'Circular_GetTransactionbyID_' . $this->NETWORK_NODE;

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);

        try {
            $response = file_get_contents($url, false, $context);
            if ($response === false) {
                throw new Exception('Network response was not ok: ' . error_get_last()['message']);
            }

            $jsonResponse = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $jsonResponse;
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Submit data to the blockchain
     * @param string $blockchain Blockchain address
     * @param string $address Account address
     * @return array Response data
     * @throws Exception If account is not open
     */
    public function submitCertificate(string $pdata, string $privateKey): array {
        if (empty($this->address)) {
            throw new Exception("Account is not open");
        }
    
        // Prepare the payload object
        $PayloadObject = [
            "Action" => "CP_CERTIFICATE",
            "Data" => stringToHex($pdata)
        ];
        
        // Convert payload object to JSON and then to hex
        $jsonstr = json_encode($PayloadObject);
        $Payload = stringToHex($jsonstr);
    
        // Get the current timestamp
        $Timestamp = getFormattedTimestamp();
    
        // Create the string for hashing
        $str = hexFix($this->blockchain) . hexFix($this->address) . hexFix($this->address) . $Payload . $this->Nonce . $Timestamp;
    
        // Generate the ID using SHA-256
        $ID = hash('sha256', $str);
    
        // Sign the data
        $Signature = $this->signData($ID, $privateKey, $this->address);
    
        // Prepare the data for the request
        $data = [
            "ID" => $ID,
            "From" => hexFix($this->address),
            "To" => hexFix($this->address),
            "Timestamp" => $Timestamp,
            "Payload" => $Payload,
            "Nonce" => strval($this->Nonce),
            "Signature" => $Signature,
            "Blockchain" => hexFix($this->blockchain),
            "Type" => 'C_TYPE_CERTIFICATE',
            "Version" => $this->codeVersion
        ];
    
        // Prepare the URL
        $url = $this->NAG_URL . 'Circular_AddTransaction_' . $this->NETWORK_NODE;

        // Use cURL to send the request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: " . $error);
        }
    
        // Get the HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        // Check if the response is successful
        if ($httpCode !== 200) {
            throw new Exception("Network response was not ok. HTTP Code: " . $httpCode);
        }
    
        // Decode the JSON response
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
    
        return $responseData;
    }

    /**
     * Recursively poll for transaction finality
     * @param string $TxID Transaction ID
     * @param int $intervalSec Polling interval in seconds
     * @return array Transaction outcome
     * @throws Exception If timeout is exceeded
     */
    public function getTransactionOutcome(string $TxID, int $timeout) {

        $startTime = time();

        while (true) {
            $elapsedTime = time() - $startTime;
            if ($elapsedTime > $timeout) {
                print_r($transactionData);
                throw new Exception('Timeout exceeded');
            }

            $transactionData = $this->getTransactionbyID($TxID, 0, 10);
            if ($transactionData && $transactionData['Result'] === 200 &&
                $transactionData['Response'] !== 'Transaction Not Found' &&
                $transactionData['Response']['Status'] !== 'Pending') {
                return $transactionData['Response'];
            }

            sleep($this->intervalSec);
        }

    }
}
