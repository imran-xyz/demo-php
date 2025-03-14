<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Your IP Address: </h2>
    <p>
        <?php
            include 'backend.php';
            echo"\nhello world";
            echo "\nIP Address $targetIP belongs to country: $countryCode";
        ?>
    </p>
</body>
</html>