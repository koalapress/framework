<?php

namespace KoalaPress\Model\Traits;
use KoalaPress\Model\Taxonomy\Model as Taxonomy;
use KoalaPress\Model\Casts\ACF;

trait HasACF
{
	/**
	 * Initialize the HasACF trait
	 */
	public function initializeHasACF(): void
	{
		self::retrieved(function ($model) {
			$acf_fields = collect(get_field_objects($this->getAcfKey(), false, false))->keys();
			$native_fields = collect($this->getAttributes())->keys();
			$acf_casts = $acf_fields->diff($native_fields)->mapWithKeys(function (string $item) {
				return [$item => ACF::class ];
			});
			$this->mergeCasts($acf_casts->toArray());
		});
	}

	/**
	 * return the acf key following ACFs convention for the current post or term
	 */
	public function getAcfKey(): ?string
	{
		return is_a($this, Taxonomy::class) ? 'term_' . $this->getAttribute('term_id') : $this->getAttribute('ID');
	}

	/**
	 * return the acf fields for the current post or term
	 */
	public function getAcfAttribute()
	{
		return get_fields($this->ID);
	}
}
