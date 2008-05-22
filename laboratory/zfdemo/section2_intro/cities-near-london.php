<html><head><title>Distance to London</title></head>
<!-- STAGES 1-7 :( -->
<body>
<?php
$cities = parse_ini_file('cities.ini'); // $cities contains our simple data model
foreach($cities as $city => $distance) {
    // business logic specifies to filter the data model satisfying distance criteria
    if ($distance < intval($_REQUEST['distance'])) {
        echo "Distance from London, UK to $city is $distance km.<br>\n";
    }
}
?>
</body>
</html>
