<?php
/******************************************************************************* 

        CIRCULAR Enterprise APIs for Data Certification
         
        License : Open Source for private and commercial use
                     
        CIRCULAR GLOBAL LEDGERS, INC. - USA
                     
        Version : 1.0.1
                     
        Creation: 3/3/2025
        Update  : 3/3/2025
                  
        Originator: Gianluca De Novi, PhD
        Contributors: Danny De Novi           
        
*******************************************************************************/

namespace Circularprotocol\Circularenterpriseapis;

use Elliptic\EC;
use DateTime;
use DateTimeZone;
use Exception;

// Global Constants
define('LIB_VERSION', '1.0.13');
define('NETWORK_URL', 'https://circularlabs.io/network/getNAG?network=');
define('DEFAULT_CHAIN', '0x8a20baa40c45dc5055aeb26197c203e576ef389d9acb171bd62da11dc5ad72b2');
define('DEFAULT_NAG', 'https://nag.circularlabs.io/NAG.php?cep=');

// HELPER FUNCTIONS

/**
 * Function to add a leading zero to numbers less than 10
 * @param int $num Number to pad
 * @return string Padded number
 */
function padNumber(int $num): string {
    return $num < 10 ? '0' . $num : (string)$num;
}

/**
 * Function to get the current timestamp in the format YYYY:MM:DD-HH:MM:SS
 * @return string Formatted timestamp
 */
function getFormattedTimestamp(): string {
    $date = new DateTime('now', new DateTimeZone('UTC'));
    return $date->format('Y:m:d-H:i:s');
}

/**
 * Removes '0x' from hexadecimal numbers if they have it
 * @param string $hex Hexadecimal string
 * @return string Cleaned hexadecimal string
 */
function hexFix(string $hex): string {
    return str_replace('0x', '', $hex);
}

/**
 * Convert a string to its hexadecimal representation without '0x'
 * @param string $string Input string
 * @return string Hexadecimal representation
 */
function stringToHex(string $string): string {
    return hexFix(bin2hex($string));
}

/**
 * Convert a hexadecimal string to a regular string
 * @param string $hex Hexadecimal string
 * @return string Regular string
 */
function hexToString(string $hex): string {
    return hex2bin($hex);
}
