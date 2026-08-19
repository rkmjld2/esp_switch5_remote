<?php

echo "<pre>";

echo "DB_HOST: ";
echo getenv("DB_HOST") ? "RECEIVED" : "MISSING";
echo "\n";

echo "DB_USER: ";
echo getenv("DB_USER") ? "RECEIVED" : "MISSING";
echo "\n";

echo "DB_PASSWORD: ";
echo getenv("DB_PASSWORD") ? "RECEIVED" : "MISSING";
echo "\n";

echo "DB_NAME: ";
echo getenv("DB_NAME") ? "RECEIVED" : "MISSING";
echo "\n";

echo "DB_PORT: ";
echo getenv("DB_PORT") ? "RECEIVED" : "MISSING";
echo "\n";

echo "ADMIN_PASSWORD: ";
echo getenv("ADMIN_PASSWORD") ? "RECEIVED" : "MISSING";
echo "\n";

echo "</pre>";

exit;
?>
