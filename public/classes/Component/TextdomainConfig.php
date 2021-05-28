<?php


namespace Palasthotel\ProcessLog\Component;


/**
 * @version 0.1.0
 * @property string domain
 * @property string languages
 */
class TextdomainConfig {

	public function __construct(string $domain, string $relativeLanguagesPath) {
		$this->domain = $domain;
		$this->languages = $relativeLanguagesPath;
	}
}