<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\inline;

/**
 * Adds Underscore inline elements
 */
trait UnderscoreTrait
{
	/**
	 * Parses the strikethrough feature.
	 * @marker _
	 */
	protected function parseUnderscore($markdown)
	{
		if (preg_match('/^_(.+?)_/', $markdown, $matches)) {
			return [
				[
					'underscore',
					$this->parseInline($matches[1])
				],
				strlen($matches[0])
			];
		}
		return [['text', $markdown[0] . $markdown[1]], 1];
	}

	protected function renderUnderscore($block)
	{
		return '<u>' . $this->renderAbsy($block[1]) . '</u>';
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($blocks);
}
