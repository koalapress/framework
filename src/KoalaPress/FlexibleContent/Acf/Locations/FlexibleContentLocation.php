<?php

namespace KoalaPress\FlexibleContent\Acf\Locations;

if (!class_exists('ACF_Location')) {
    exit; // Exit if acf is not present.
}

use ACF_Location;

class FlexibleContentLocation extends ACF_Location
{
    /**
     * Initialize the location with its name and label.
     */
	public function initialize(): void
	{
		$this->name = 'flexible_content';
		$this->label = __('Flexible content');
        $this->category = __('Flexible content');
	}

    /**
     * Get the values for the flexible content location.
     *
     * @param string $rule The rule for which to get values.
     * @return array An array of values for the flexible content location.
     */
	public function get_values($rule): array
	{
		$values = [];

		$values['section'] = __('Use as module');
		$values['section_options'] = __('Use as section options');

		return array_unique($values);
	}
}
