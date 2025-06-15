<?php

namespace KoalaPress\Model\Traits;

use function KoalaPress\Database\PostType\Traits\get_edit_post_link;

trait HasAdminColumns
{
	/**
	 * Get the column value for the admin table
	 */
	public function getColumn($which): string
	{
		$value = $this->{strtolower($which)};

		return '<a href="' . get_edit_post_link($this->ID) . '">' . $value . '</a>';
	}
}
