<?php

namespace KoalaPress\Support\Helper;

use Illuminate\Support\Str;

class NamingHelper
{
    /**
     * @param string $className
     * @param string|null $prefix
     * @param string|null $suffix
     * @return string
     */
    public static function getShortName(string $className, ?string $prefix = null, ?string $suffix = null): string
    {
        // Remove the namespace part of the class name
        $parts = explode('\\', $className);
        $shortName = end($parts);


        if ($prefix) {
            // Remove the prefix if it exists
            $shortName = Str::replaceFirst($prefix, '', $shortName);
        }

        if ($suffix) {
            // Remove the suffix if it exists
            $shortName = Str::replaceLast($suffix, '', $shortName);
        }

        return $shortName;
    }
}
