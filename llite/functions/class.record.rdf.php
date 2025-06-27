<?php

class RDF {
	
	public $record;
	public $errors = [];
	public $defaultValues;
	public $relRec;
	
	
	public function __construct() {
		$this->relRec = new stdClass;
		$this->defaultValues = new stdClass;
		}
		
	
	function RDF2Array(DOMNode $node) {
		// Jeśli węzeł jest tekstowy – zwróć przyciętą wartość.
		if ($node->nodeType === XML_TEXT_NODE) {
			return trim($node->nodeValue);
		}
		
		// Sprawdzamy, czy węzeł posiada choć jedno dziecko będące elementem.
		$hasElementChild = false;
		foreach ($node->childNodes as $child) {
			if ($child->nodeType === XML_ELEMENT_NODE) {
				$hasElementChild = true;
				break;
			}
		}
		
		$result = [];
		
		// Dodajemy atrybuty (jeśli występują) z prefiksem '@'
		if ($node->hasAttributes()) {
			foreach ($node->attributes as $attr) {
				$result["@{$attr->name}"] = $attr->value;
			}
		}
		
		// Jeśli nie ma dzieci typu element:
		if (!$hasElementChild) {
			$text = trim($node->textContent);
			if ($text !== '') {
				// Jeśli nie ma atrybutów – zwracamy skalarny tekst
				if (empty($result)) {
					return $text;
				} else {
					// Gdy mamy zarówno atrybuty, jak i tekst, zapisujemy tekst pod kluczem '#text'
					$result['#text'] = $text;
				}
			}
			// Zwracamy tablicę (nawet jeśli zawiera jedynie atrybuty)
			return $result;
		}
		
		// Przetwarzamy dzieci (tylko elementy)
		foreach ($node->childNodes as $child) {
			if ($child->nodeType !== XML_ELEMENT_NODE) {
				continue;
			}
			
			// Pobieramy nazwę dziecka – usuwamy ewentualny prefix (np. 'rdf:' czy 'dcterms:')
			$childName = $child->nodeName;
			$parts = explode(':', $childName);
			$childName = (count($parts) > 1) ? $parts[1] : $childName;
			
			// Rekurencyjnie przetwarzamy dziecko
			$childValue = $this->RDF2Array($child);
			
			// Jeśli dla danego klucza już istnieje jakaś wartość, konwertujemy ją na tablicę
			if (isset($result[$childName])) {
				if (!is_array($result[$childName]) || (is_array($result[$childName]) && !isset($result[$childName][0]))) {
					$result[$childName] = [$result[$childName]];
				}
				$result[$childName][] = $childValue;
			} else {
				$result[$childName] = $childValue;
			}
		}
		
		return $result;
		}
	
	
	}
	
?>	