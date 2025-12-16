<?php

return [
    'admin_emails' => array_filter(array_map('trim', explode(',', env('ENIGMACERO_ADMIN_EMAILS', '')))),
];
