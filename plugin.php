<?php
class PulponairSemanticImages extends KokenPlugin {
	const PROPERTY_SETTING_PREFIX = 'property_';

	/**
	 * Item property map
	 *
	 * @var array
	 */
	protected $itemPropertyMap = array(
		'dateCreated' => 'image/captured_on/timestamp',
		'datePublished' => 'image/published_on/timestamp',
		'author' => 'profile/name',
	);

	/**
	 * Constructor registers filter
	 *
	 */
	public function __construct() {
		$this->require_setup = true;
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
			$context = array(
				'profile' => Koken::$profile
			);
			$search = array();
			$replace = array();
			for ($i = 0; $i < $imageCount; $i++) {
				$search[] = $matches[0][$i];
				$context['image'] = Koken::api('/content/index/' . (int)$matches[1][$i]);
				$replace[] = $this->wrapByItemScopeTag(
					$matches[0][$i] .
					$this->mapContextToItemPropertiesTags($context) ,
					'ImageObject');
			}
			$content = str_replace($search, $replace, $content);
		}
		return $content;
	}

	/**
	 * Maps the context to item property tags. Taking the configuration into account
	 *
	 * @param array $context
	 * @return string
	 */
	protected function mapContextToItemPropertiesTags($context) {
		$result = '';

		foreach($this->data as $key => $value) {
			if (strpos($key, self::PROPERTY_SETTING_PREFIX) !== 0) {
				continue;
			}

			if ($value) {
				$itemProperty = substr($key, strlen(self::PROPERTY_SETTING_PREFIX));

				if ($path = $this->itemPropertyMap[$itemProperty]) {
					$content = $this->getArrayElementByPath($context, $path);
				} else {
					$content = $this->getArrayElementByPath($context, $value);
				}

				if (strpos($itemProperty,'date') === 0) {
					$content = date('Y-m-d', $content);
				}

				if (!empty($content)) {
					$result .= $this->createItemPropertyTag($itemProperty, $content);
				}
			}
		}
		return $result;
	}

	/**
	 * Gets an array element by path
	 *
	 * @param array $source
	 * @param string $path
	 * @param string $pathDelimiter
	 * @return mixed
	 */
	protected function getArrayElementByPath($source, $path, $pathDelimiter = '/') {
		$explodedPath = explode($pathDelimiter, $path);
		$pointer = &$source;
		foreach ($explodedPath as $segment) {
			$pointer = &$pointer[$segment];
		}

		return $pointer;
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