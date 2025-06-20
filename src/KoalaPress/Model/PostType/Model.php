<?php

namespace KoalaPress\Model\PostType;

use Carbon\Carbon;
use Corcel\Model\Post as Corcel;
use Illuminate\Support\Facades\Cache;
use KoalaPress\Model\Traits\ForwardsNamedCalls;
use KoalaPress\Model\Traits\HasACF;
use KoalaPress\Model\Traits\HasAdminColumns;
use ReflectionClass;
use Stephenjude\DefaultModelSorting\Traits\DefaultOrderBy;


class Model extends Corcel
{
	use ForwardsNamedCalls;
	use HasAdminColumns;
	use HasACF;
	use DefaultOrderBy;

	/**
	 * set the default order by column
	 *
	 * @var string
	 */
	protected static $orderByColumn = 'post_date';

	/**
	 * set the default order by column direction
	 *
	 * @var string
	 */
	protected static $orderByColumnDirection = 'desc';

    /**
     * Set the default casts for the model.
     *
     * @var string[]
     */
	public $casts = [
			'post_date' => 'datetime',
			'post_modified' => 'datetime',
		];

	/**
	 * The icon for the post type @see https://developer.wordpress.org/resource/dashicons/
	 */
	public string $icon = 'editor-quote';

    /**
     * Will this post type use dynamic content?
     *
     * @var bool
     */
    public $useDymamicContent = false;

	/**
	 * Names for the post type @see https://posttypes.jjgrainger.co.uk/post-types/create-a-post-type
	 */
	public array $names = [];

	/**
	 * Labels for the post type @see https://posttypes.jjgrainger.co.uk/post-types/create-a-post-type
	 */
	public array $labels = [];

	/**
	 * Options for the post type @see https://posttypes.jjgrainger.co.uk/post-types/create-a-post-type
	 */
	public array $options = [];

	/**
	 * Add custom columns to the admin table
	 */
	public array $adminColumns = [];

	/**
	 * Remove columns from the admin table
	 */
	public array $adminColumnsHidden = [];

	/**
	 * The post type
	 *
	 * @var array
	 *
	 * @return void
	 */
	public function __construct(array $attributes = [])
	{
		$reflection = new ReflectionClass($this);

		// If the current class is the base model, set the post type to false
		if ($reflection->getName() === self::class) {
			$this->postType = false;
		}

		// If the post type is not set, set it to the class name
		if ($this->postType === null) {
			$reflection = new ReflectionClass($this);
			$this->postType = strtolower($reflection->getShortName());
		}

		// Translate the labels
		foreach ($this->labels as &$label) {
			$label = __($label);
		}

		// Set the post type on attributes
		$this->setRawAttributes(
			array_merge(
				$this->attributes,
				[
					'post_type' => $this->getPostType(),
				]
			),
			true
		);

		parent::__construct($attributes);
	}

	/**
	 * Append all mutated attributes to the array representation of this post
	 */
	public function toArray(): array
	{
		$array = parent::toArray();

		foreach ($this->getMutatedAttributes() as $key) {
			if (!array_key_exists($key, $array)) {
				$array[$key] = $this->{$key};
			}
		}

		if (isset($this->hidden) && is_array($this->hidden)) {
			foreach ($this->hidden as $k) {
				unset($array[$k]);
			}
		}

		return $array;
	}

	/**
	 * Get the post date as carbom´n instance
	 *
	 * @return Carbon
	 */
	public function getPostDateAttribute($value)
	{
		$value = Carbon::parse($value);

		return $value;
	}

	/**
	 * Get the permalink for the post
	 *
	 * @return false|string
	 */
	public function getPermalinkAttribute(): bool|string
	{
		return Cache::get('post_permalink_' . $this->ID, function () {
			return get_permalink($this->ID);
		});
	}
}
