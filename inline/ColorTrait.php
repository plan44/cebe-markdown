<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 *
 * ColorTrait: Copyright (c) 2022 luz@plan44.ch
 */

namespace cebe\markdown\inline;

/**
 * Adds color inline elements
 */
trait ColorTrait
{
	/**
	 * Parses the strikethrough feature.
	 * @marker _(
	 */
	protected function parseColor($markdown)
	{
		if (preg_match('/^_\((.+?)\)(.+?)_/', $markdown, $matches)) {
			return [
				[
					'color',
					$this->parseInline($matches[2]), // the colored content
					$matches[1], // the color
				],
				strlen($matches[0])
			];
		}
		return [['text', $markdown[0] . $markdown[1]], 2];
	}

	protected function renderColor($block)
	{
		return sprintf(
			'<span style="color: %s;">%s</span>',
			$block[2],
			$this->renderAbsy($block[1])
		);
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($blocks);
}
