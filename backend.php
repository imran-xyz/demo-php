<?php
ini_set('memory_limit', '2048M');

function getUserIp() {
    return $_SERVER['REMOTE_ADDR'] ?? false;
}

// Get the IP address
$user_ip = getUserIp();

// Redirect if the IP is not detected
if (!$user_ip || !filter_var($user_ip, FILTER_VALIDATE_IP)) {
    header("Location: error.php");
    exit();
}

function getCountryCode($targetIP, $csvFile) {
    // Redirect to error page if any issue occurs
    try {
        // Validate IP format
        if (!filter_var($targetIP, FILTER_VALIDATE_IP)) {
            throw new Exception("Invalid IP address");
        }

        // Check if file exists
        if (!file_exists($csvFile) || !is_readable($csvFile)) {
            throw new Exception("Error: CSV file not found or not readable");
        }

        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            throw new Exception("Error opening CSV file");
        }

        // Skip header row
        fgetcsv($handle);

        $ipRanges = [];

        // Read and store IP ranges
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;

            $startIP = trim($row[0]);
            $endIP = trim($row[1]);
            $countryCode = trim($row[2]);

            if (!filter_var($startIP, FILTER_VALIDATE_IP) || !filter_var($endIP, FILTER_VALIDATE_IP)) {
                continue;
            }

            // Convert IPs to comparable formats
            $startLong = filter_var($startIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? sprintf('%u', ip2long($startIP)) : inet_pton($startIP);
            $endLong = filter_var($endIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? sprintf('%u', ip2long($endIP)) : inet_pton($endIP);
            $targetLong = filter_var($targetIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? sprintf('%u', ip2long($targetIP)) : inet_pton($targetIP);

            $ipRanges[] = [$startLong, $endLong, $countryCode];
        }

        fclose($handle);

        // Sort the ranges for efficient searching
        usort($ipRanges, function ($a, $b) {
            return strcmp($a[0], $b[0]);
        });

        // Search for the target IP in the ranges
        foreach ($ipRanges as [$startLong, $endLong, $countryCode]) {
            if ($targetLong >= $startLong && $targetLong <= $endLong) {
                if ($countryCode !== "US") {
                    throw new Exception("Unauthorized country");
                }
                return $countryCode;
            }
        }

        throw new Exception("Country not found");
    } catch (Exception $e) {
        header("Location: error.php");
        exit();
    }
}

// Example usage
$targetIP = '52.124.246.1';
$csvFile = 'country_asn.csv';
$countryCode = getCountryCode($user_ip, $csvFile);
?>
