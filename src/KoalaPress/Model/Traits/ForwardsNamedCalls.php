<?php

namespace KoalaPress\Model\Traits;

trait ForwardsNamedCalls
{
	/**
	 * Add custom callback methods
	 *
	 * @return mixed|string
	 */
	public function __call($method, $parameters)
	{
		$parts = preg_split('/(?=[A-Z0-9])/', $method);

		if (count($parts) === 3 && method_exists($this, $parts[0] . $parts[2])) {
			return $this->{$parts[0] . $parts[2]}($parts[1]);
		}

		return parent::__call($method, $parameters);
	}
}
