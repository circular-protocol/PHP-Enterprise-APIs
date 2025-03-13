# Circular Enterprise PHP APIs for Data Certification

This repository provides PHP implementations of the **Circular Enterprise APIs** for data certification and blockchain integration. It includes classes and helper functions to interact with the Circular network, submit certificates, retrieve transactions, and manage accounts.

## Features

- **Data Certification**: Submit data to the blockchain for certification.
- **Transaction Retrieval**: Fetch transaction details by ID or block range.
- **Account Management**: Open, update, and close accounts.
- **Secure Signing**: Sign data using elliptic curve cryptography (secp256k1).
- **Network Integration**: Connect to different blockchain networks (mainnet, testnet, devnet).

---

## Installation

You can install this library using **Composer** or by **cloning the repository**.

### Option 1: Install via Composer (Recommended)

1. Run the following command in your project directory:
   ```bash
   composer require circularprotocol/circular-enterprise-apis
   ```

2. Include the Composer autoloader in your PHP script:
   ```php
   require_once __DIR__ . '/vendor/autoload.php';
   ```

### Option 2: Clone the Repository

1. Clone the repository:
   ```bash
   git clone https://github.com/circular-protocol/circular-php.git
   cd circular-enterprise-apis
   ```

2. Install dependencies using Composer:
   ```bash
   composer install
   ```

3. Include the autoloader in your PHP script:
   ```php
   require_once __DIR__ . '/vendor/autoload.php';
   ```

---

## Usage

### 1. Opening an Account
To open an account and retrieve account details:
```php
use Circularprotocol\Circularenterpriseapis\CEP_Account;

$account = new CEP_Account();
$account->open('a4fc8b11bfc5dc2911ab41871e6de81f500fe60f3961343b202ad78e7e297ea08');

echo "Account opened successfully.\n";
```

### 2. Submitting a Certificate
To submit data for certification:
```php
$pdata = 'Your data to certify';
$privateKey = 'your_private_key_here';

try {
    $response = $account->submitCertificate($pdata, $privateKey);
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### 3. Retrieving a Transaction
To fetch transaction details by ID:
```php
$TxID = 'your_transaction_id_here';
$Start = 0; // Starting block
$End = 10;  // Ending block

try {
    $transaction = $account->getTransactionbyID($TxID, $Start, $End);
    print_r($transaction);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### 4. Setting the Blockchain Network
To switch between networks (e.g., mainnet, testnet):
```php
$networkUrl = $account->setNetwork('mainnet');
echo "Network URL set to: " . $networkUrl . "\n";
```

---

## Helper Functions

- **`hexFix(string $hex)`**: Removes `0x` from hexadecimal strings.
- **`stringToHex(string $string)`**: Converts a string to its hexadecimal representation.
- **`hexToString(string $hex)`**: Converts a hexadecimal string back to a regular string.
- **`getFormattedTimestamp()`**: Returns the current timestamp in `YYYY:MM:DD-HH:MM:SS` format.

---

## Example Workflow

1. Open an account.
2. Submit data for certification.
3. Retrieve the transaction ID from the response.
4. Fetch transaction details using the transaction ID.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php'; // IF NEEDED
require_once __DIR__ . '/src/CircularEnterpriseAPIS-php'; // IF NEEDED

use Circularprotocol\Circularenterpriseapis\CEP_Account;

try {
    // Instantiate the CEP Account class
    $account = new CEP_Account();
    echo "CEP_Account instantiated successfully.\n";

    $address = 'your-account-address';
    $pk = 'your-private-key'; // Note: pk should be a string, not a hex literal
    $blockchain = 'blockchain-address'; // Same here, string.
    $txID = ''; // Initialize txID
    $txBlock = ''; // Initialize txBlock

    $account->setNetwork("testnet"); // chose between multiple networks such as testnet, devnet and mainnet
    $account->setBlockchain($blockchain);
    echo "Test variables set.\n";

    if ($account->open($address)) {
        echo "Account opened successfully.\n";

        if ($account->updateAccount()) {
            echo "Nonce: " . $account->Nonce . "\n";

            $txIDTemp = $account->submitCertificate("your-data-to-certificate", "1de4fc4d951349a382c9ca58e6c82feeed8a233657683455080338675ad7e61f");
            $txID = $txIDTemp["Response"]["TxID"];
            echo "TxID: " . $txID . "\n";

            $resp = $account->getTransactionOutcome($txID, 10);
            $blockID = $resp["BlockID"];
            $status = $account->getTransaction($blockID, $txID);

            echo "Transaction Status: " . $status["Response"]["Status"] . "\n";

            $account->close();
        } else {
            echo "Update Account Error: " . $account->lastError . "\n";
        }
    } else {
        echo "Failed to open account: " . $account->lastError . "\n";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}

?>
```

---

## Contributing

Contributions are welcome! If you'd like to contribute, please follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Submit a pull request with a detailed description of your changes.

---

## License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

## Support

For questions or issues, please open an issue on the [GitHub repository](https://github.com/circular-protocol/circular-php/issues).

---

This version of the `README.md` includes installation via Composer, making it easier for users to integrate the library into their projects. It also provides clear examples and a structured workflow for using the API. Let me know if you need further adjustments!
