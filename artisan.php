<?php
use Illuminate\Support\Facades\Artisan;

// Uncomment one of the lines below depending on your need
Artisan::call('route:clear');
// Artisan::call('cache:clear');
// Artisan::call('config:clear');
// Artisan::call('view:clear');

echo "Routes cleared!";
