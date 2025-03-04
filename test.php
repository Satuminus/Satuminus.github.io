<?php

//Key:d4954e8b2e444fc89a89a463788c0a72
//Ladestation: 254633 (Grotefeld)
//			   710222 (Edeka)
//updatedAt --> zuletzt benutzt?
//openingTimes

// API Endpoints and Keys
$apiEndpointGrotefeld = 'https://enbw-emp.azure-api.net/emobility-public-api/api/v1/chargestations/254633';
$apiEndpointEdeka = 'https://enbw-emp.azure-api.net/emobility-public-api/api/v1/chargestations/710222'; //Edeka
$subscriptionKey = 'd4954e8b2e444fc89a89a463788c0a72';
$userAgent = 'test';
$origin = 'https://www.enbw.com';
$referer = 'https://www.enbw.com/';

$headers = [
    'User-Agent: ' . $userAgent,
    'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
    'Origin: ' . $origin,
    'Referer: ' . $referer,
];

// Function to fetch and decode data from API
function fetchData($apiEndpoint, $headers) {
    $curl = curl_init($apiEndpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'Curl error: ' . curl_error($curl);
        return null;
    } else {
        $data = json_decode($response, true);
        curl_close($curl);
        return $data;
    }
}

// Fetch data for both stations
$dataGrotefeld = fetchData($apiEndpointGrotefeld, $headers);
$dataEdeka = fetchData($apiEndpointEdeka, $headers);

// Initialize station information
$stationInfoGrotefeld = [
    'freeSlots' => null,
    'alwaysOpen' => false,
    'chargePoints' => [],
    'numberOfChargePoints' => 0
];

$stationInfoEdeka = [
    'freeSlots' => null,
    'alwaysOpen' => false,
    'chargePoints' => [],
    'numberOfChargePoints' => 0
];

// Process Grotefeld data
if ($dataGrotefeld) {
    $stationInfoGrotefeld['freeSlots'] = $dataGrotefeld['availableChargePoints'] ?? 0;
    $stationInfoGrotefeld['alwaysOpen'] = $dataGrotefeld['alwaysOpen'] ?? false;
    $stationInfoGrotefeld['chargePoints'] = $dataGrotefeld['chargePoints'] ?? [];
    $stationInfoGrotefeld['numberOfChargePoints'] = $dataGrotefeld['numberOfChargePoints'] ?? 0;
}

// Process Edeka data
if ($dataEdeka) {
    $stationInfoEdeka['freeSlots'] = $dataEdeka['availableChargePoints'] ?? 0;
    $stationInfoEdeka['alwaysOpen'] = $dataEdeka['alwaysOpen'] ?? false;
    $stationInfoEdeka['chargePoints'] = $dataEdeka['chargePoints'] ?? [];
    $stationInfoEdeka['numberOfChargePoints'] = $dataEdeka['numberOfChargePoints'] ?? 0;
}

// Function to display charger info in a table
function displayChargerInfoTable($stationName, $stationInfo) {
    echo "<h3>Ladestation: $stationName</h3>";

    echo "<p>Freie Ladestationen: " . $stationInfo['freeSlots'] . " von " . $stationInfo['numberOfChargePoints'] . " frei";
    if ($stationInfo['alwaysOpen']) {
        echo " (24/7 geöffnet)";
    }
    echo "</p>";

    echo "<table style='border-collapse: collapse;'>"; // removes visible lines

    echo "<tr><th>Station ID</th><th>Status</th><th>Power</th><th>Type</th></tr>";

    foreach ($stationInfo['chargePoints'] as $chargePoint) {
        $evseId = $chargePoint['evseId'];
        $status = $chargePoint['status'];
        $maxPowerInKw = $chargePoint['connectors'][0]['maxPowerInKw'];
        $plugTypeName = $chargePoint['connectors'][0]['plugTypeName'];

        echo "<tr>";
        echo "<td>$evseId</td>";
        echo "<td>";
        if ($status === 'AVAILABLE') {
            echo "<span style='background-color: green; color: white; padding: 5px;'>AVAILABLE</span>";
        } else {
            echo "<span style='background-color: red; color: white; padding: 5px;'>OCCUPIED</span>";
        }
        echo "</td>";
        echo "<td>$maxPowerInKw kW</td>";
        echo "<td>$plugTypeName</td>";
        echo "</tr>";
    }

    echo "</table><br>";
}

// Display information for both stations in a table
displayChargerInfoTable("Autohaus Grotefeld", $stationInfoGrotefeld);
displayChargerInfoTable("Edeka Barkhausen", $stationInfoEdeka);

?>
