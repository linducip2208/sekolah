<?php

return [
    'base_domain'        => env('TENANT_BASE_DOMAIN', 'eschool.whitelabel.co.id'),
    'subdomain_pattern'  => '{school}.' . env('TENANT_BASE_DOMAIN', 'eschool.whitelabel.co.id'),
    'super_admin_domain' => env('SUPER_ADMIN_DOMAIN', 'admin.eschool.whitelabel.co.id'),
    'default_timezone'   => 'Asia/Jakarta',
    'default_locale'     => 'id',
    'grace_period_days'  => 7,
];
