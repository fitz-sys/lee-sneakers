<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$lat = $input['lat'] ?? null;
$lng = $input['lng'] ?? null;

if (!$lat || !$lng) {
    echo json_encode(['error' => 'Invalid coordinates']);
    exit;
}

// Define Metro Manila polygon (approximate boundary coordinates - adjust for precision)
$metroManilaPolygon = [
    ['lat' => 14.758, 'lng' => 121.047],  // North
    ['lat' => 14.599, 'lng' => 121.078],  // East
    ['lat' => 14.417, 'lng' => 121.003],  // South-East
    ['lat' => 14.408, 'lng' => 120.983],  // South
    ['lat' => 14.467, 'lng' => 120.973],  // South-West
    ['lat' => 14.599, 'lng' => 120.983],  // West
    ['lat' => 14.758, 'lng' => 121.047]   // Back to North
];

// Function to check if point is inside polygon (Ray Casting Algorithm)
function isPointInPolygon($point, $polygon) {
    $x = $point['lng'];
    $y = $point['lat'];
    $inside = false;
    $n = count($polygon);
    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        if (($polygon[$i]['lng'] > $x) != ($polygon[$j]['lng'] > $x) &&
            ($y < ($polygon[$j]['lat'] - $polygon[$i]['lat']) * ($x - $polygon[$i]['lng']) / ($polygon[$j]['lng'] - $polygon[$i]['lng']) + $polygon[$i]['lat'])) {
            $inside = !$inside;
        }
    }
    return $inside;
}

// Check if coordinates are within Metro Manila
$withinMetroManila = isPointInPolygon(['lat' => $lat, 'lng' => $lng], $metroManilaPolygon);

echo json_encode(['withinMetroManila' => $withinMetroManila]);
?>