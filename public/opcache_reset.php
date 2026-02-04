<?php
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPCACHE RESET SUCCESS";
    } else {
        echo "OPCACHE RESET FAILED";
    }
} else {
    echo "OPCACHE NOT ENABLED";
}
