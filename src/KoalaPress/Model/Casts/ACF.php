<?php

namespace KoalaPress\Model\Casts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ACF implements CastsAttributes
{
	/**
	 * @inheritDoc
	 */
	public function get(Model $model, string $key, mixed $value, array $attributes)
	{
		return get_field($key, $model->getAcfKey());
	}

	/**
	 * @inheritDoc
	 */
	public function set(Model $model, string $key, mixed $value, array $attributes): void
	{
		update_field($key, $value, $model->getAcfKey());
	}
}
