<?php

namespace KoalaPress\Model\Taxonomy;

use Corcel\Model\Taxonomy as Corcel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use KoalaPress\Model\Traits\ForwardsNamedCalls;
use KoalaPress\Model\Traits\HasACF;
use KoalaPress\Model\Traits\HasAdminColumns;
use ReflectionClass;

class Model extends Corcel
{
    use ForwardsNamedCalls;
    use HasAdminColumns;
    use HasACF;

    /**
     * Names for the post taxonomy @see https://posttypes.jjgrainger.co.uk/taxonomies/create-a-taxonomy
     */
    public array $names = [];

    /**
     * Options for the post taxonomy @see https://posttypes.jjgrainger.co.uk/taxonomies/create-a-taxonomy
     */
    public array $options = [];

    /**
     * Labels for the post taxonomy @see https://posttypes.jjgrainger.co.uk/taxonomies/create-a-taxonomy
     */
    public array $labels = [];

    /**
     * The related PostTypes @see https://posttypes.jjgrainger.co.uk/taxonomies/add-to-post-type
     */
    public array $postTypes = [];

    /**
     * Should that taxonomy be unique?
     */
    public bool $unique = false;

    /**
     * Should that taxonomy be hierarchical?
     */
    public bool $hierarchical = true;

    /**
     * Add custom columns to the admin table
     */
    public array $admin_columns = [];

    /**
     * Remove columns from the admin table
     */
    public array $admin_columns_hidden = [];

    /**
     * The taxonomy name
     */
    protected ?string $taxonomy = null;

    public function __construct(array $attributes = [])
    {
        if ($this->taxonomy == null) {
            $reflection = new ReflectionClass($this);
            $this->taxonomy = strtolower($reflection->getShortName());
        }

        if (count($this->labels)) {
            foreach ($this->labels as &$label) {
                $label = __($label);
            }
        }
        parent::__construct($attributes);
    }

    /**
     * Get the taxonomy name
     */
    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }

    /**
     * Get the name attribute
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getTaxonomy();
    }

    /**
     * Get the term link attribute
     *
     * @return array|false|int|string|WP_Error|WP_Term|null
     */
    public function getTermLinkAttribute()
    {
        $t = get_term($this->term_id, $this->taxonomy);

        return get_term_link($t);
    }

    /**
     * Get the url attribute
     *
     * @return array|false|int|string|WP_Error|WP_Term|null
     */
    public function getUrlAttribute()
    {
        return $this->getTermLinkAttribute();
    }

    /**
     * @return mixed
     */
    public function getTitleAttribute()
    {
        return $this->term->name;
    }

    /**
     * @return mixed
     */
    public function getSlugAttribute()
    {
        return $this->term->slug;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('count', '>', 0);
    }

    /**
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            \KoalaPress\Model\PostType\Model::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        );
    }
}
