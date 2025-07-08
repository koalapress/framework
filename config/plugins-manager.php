<?php

return [
    'blacklist' => [
        !app()->isProduction() ? 'cachify' : null,
    ],
];
