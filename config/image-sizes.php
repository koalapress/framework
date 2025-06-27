<?php
// add image sizes here
// they will be automatically calculated by imageWidths and added to the theme
// if you lower the max width value all bigger sizes will be omitted
return [
    /**
     * Add fixed image sizes here.
     * These will be automatically generated as they are defined here.
     */
    'sizes' => [
        'socialmedia' => [
            'width' => 1200,
            'height' => 630,
        ],
        'max' => [
            'width' => 2000,
            'height' => 2000,
            'crop' => false,
        ],
    ],
    /**
     * Add image ratios here.
     * These will be automatically calculated based on the defined widths.
     * The maxWidth defines the maximum width for this ratio.
     */
    'ratios' => [
        '16x9' => [
            'width' => 16,
            'height' => 9,
            'maxWidth' => 2560,
        ],
        '4x3' => [
            'width' => 4,
            'height' => 3,
            'maxWidth' => 2560,
        ],
        '1x1' => [
            'width' => 1,
            'height' => 1,
            'maxWidth' => 1920,
        ],
        'variable' => [
            'width' => 2,
            'height' => null,
            'maxWidth' => 2560,
        ],
    ],
    /**
     * Define the widths for the image sizes.
     * These widths will be used to calculate the heights based on the ratios.
     */
    'widths' => [
        64,
        320,
        640,
        960,
        1280,
        1600,
        1920,
        2560,
    ],
];
