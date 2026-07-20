<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo 'PHP_VERSION='.PHP_VERSION."\n";
echo 'PHP_MAJOR='.PHP_MAJOR_VERSION."\n";
echo 'PHP_MINOR='.PHP_MINOR_VERSION."\n";
echo 'SAPI='.PHP_SAPI."\n";
echo "OK\n";
