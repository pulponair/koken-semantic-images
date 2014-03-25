<?php
class PulonairSemanticImages extends KokenPlugin {


	public function __construct() {
		$this->register_filter('site.output', 'render');
	}

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


	protected function createItemPropertyTag($property, $content, $tag = 'meta') {
		return '<' . $tag . ' itemprop="' . $property . '" content="' . $content . '" />';
	}


	protected function wrapByItemScopeTag($content, $itemType, $tag = 'span') {
		return '<' . $tag . ' itemscope itemtype="http://schema.org/' . $itemType . '">' .
			$content .
			'</' . $tag . '>';
	}

}