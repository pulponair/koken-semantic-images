<?php
class PulponairSemanticImages extends KokenPlugin {

	/**
	 * Construtor registers filter
	 *
	 */
	public function __construct() {
		$this->register_filter('site.output', 'render');
	}

	/**
	 * The actual render method. Searches for image tags and appends semantinc tags.
	 *
	 * @param string $content
	 * @return string mixed
	 */
	public function render($content) {
		$pattern = '/<img.*data-base=".*\/(.*)\/.*,.*?".+?>/';
		$imageCount = preg_match_all($pattern, $content, $matches);
		if ($imageCount) {
			$search = array();
			$replace = array();
			for ($i = 0; $i < $imageCount; $i++) {
				$search[] = $matches[0][$i];
				$image = Koken::api('/content/index/' . (int)$matches[1][$i]);
				$replace[] = $this->wrapByItemScopeTag(
					$matches[0][$i] .
					$this->createItemPropertyTag('caption', $image['title'] ? $image['title'] : $image['filename']) .
					$this->createItemPropertyTag('dateCreated',$image['captured_on']['datetime']) .
					$this->createItemPropertyTag('dateCreated',$image['published_on']['datetime']) .
					$this->createItemPropertyTag('contentURL',$image['url']),
					'ImageObject');
			}

			$content = str_replace($search, $replace, $content);
		}
		return $content;
	}

	/**
	 * Creates an item property tag.
	 *
	 * @param string $property
	 * @param string $content
	 * @param string $tag
	 * @return string
	 */
	protected function createItemPropertyTag($property, $content, $tag = 'meta') {
		return '<' . $tag . ' itemprop="' . $property . '" content="' . $content . '" />';
	}

	/**
	 * Wraps given content in an schema.org item scope
	 * 
	 * @param string $content
	 * @param sting $itemType
	 * @param string $tag
	 * @return string
	 */
	protected function wrapByItemScopeTag($content, $itemType, $tag = 'span') {
		return '<' . $tag . ' itemscope itemtype="http://schema.org/' . $itemType . '">' .
			$content .
			'</' . $tag . '>';
	}

}