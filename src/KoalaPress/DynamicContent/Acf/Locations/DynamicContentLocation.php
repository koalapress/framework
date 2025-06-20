<?php

namespace KoalaPress\DynamicContent\Acf\Locations;

if (!class_exists('ACF_Location')) {
    exit; // Exit if acf is not present.
}

use ACF_Location;

class DynamicContentLocation extends ACF_Location
{
    /**
     * Initialize the location with its name and label.
     */
	public function initialize(): void
	{
		$this->name = 'dynamic_content';
		$this->label = __('Dynamic content');
        $this->category = __('Dynamic content');
	}

    /**
     * Get the values for the dynamic content location.
     *
     * @param string $rule The rule for which to get values.
     * @return array An array of values for the dynamic content location.
     */
	public function get_values($rule): array
	{
		$values = [];

		$values['module'] = __('Use as module');
		$values['section_options'] = __('Use as section options');

		return array_unique($values);
	}
}
