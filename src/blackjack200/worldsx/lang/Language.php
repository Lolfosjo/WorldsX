<?php

namespace blackjack200\worldsx\lang;

class Language {
	public function __construct(
		private string $languageCode,
		private array  $data
	) {
	}

	public function translateString(string $key, array $args = []) : ?string {
		if (isset($this->data[$key])) {
			$str = $this->data[$key];
			foreach ($args as $k => $v) {
				$str = str_replace("[$k]", $v, $str);
			}
			return $str;
		}
		return null;
	}

	public function getLanguageCode() : string {
		return $this->languageCode;
	}
}