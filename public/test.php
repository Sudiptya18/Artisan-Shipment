<?php
echo "Project: Artisan-Shipment<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __FILE__ . "<br>";
echo "Base Path: " . realpath(__DIR__ . '/..') . "<br>";
echo "Vendor Path: " . realpath(__DIR__ . '/../vendor') . "<br>";
echo "Bootstrap Path: " . realpath(__DIR__ . '/../bootstrap/app.php') . "<br>";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "<br>";

