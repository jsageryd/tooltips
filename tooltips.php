namespace Tooltips_plugin;

/*
	Tooltips plugin for MODx
	-----------------------

	Reads one or more preambles in the form:

	|tooltips|
		something => something else
	|/tooltips|

	...and translates it to:

	|replace|
		something => \tt[something][something else]
	|/replace|

	...for the replace plugin to read.

*/

function tooltips($string){
//	Get hash of things to substitute
	$sectionscontent = sectionstohash($string, 'tooltips');

//	Return if there were no tooltips sections
	if(empty($sectionscontent)){ return $string; }

//	Build a new section, replacing all values with \tt[key][value]
	$s = "|replace|\n";
	foreach($sectionscontent as $k => $v){
		$s .= "\t$k\t=>\t\\tt[$k][$v]\n";
	}
	$s .= "|/replace|\n";

//	Prepend the new section to the string
	return $s . $string;
}

// Returns a hash with all section content and removes the section(s) from the string
function sectionstohash(&$string, $sectionidentifier){
//	Get all |sectionidentifier| |/sectionidentifier| sections
	preg_match_all("/\|$sectionidentifier\|\s*(.*)\s*\|\/$sectionidentifier\|/uisU", $string, $sections);

//	For each section, add each get each of its key-value pairs and add to $returnhash
	$returnhash = array();
	foreach($sections[1] as $section){
		preg_match_all("/\s*(.*=>.*)\s*/u", $section, $entries);
		foreach($entries[1] as $entry){
			$keyvaluepair = (preg_split("/\s*=>\s*/u", $entry));
			$returnhash[chop($keyvaluepair[0])] = chop($keyvaluepair[1]);
		}
	}

//	Remove all |sectionidentifier| |/sectionidentifier| sections from the original string
	do{	
		$laststring = $string;
		$string = preg_replace("/(\|$sectionidentifier\|.*\|\/$sectionidentifier\|\s)/uisU", '', $string);
	}while($string !== $laststring);

//	Return hash
	return $returnhash;
}

$e = &$modx->Event;
switch ($e->name) {
	case "OnLoadWebDocument":
		$o = &$modx->documentObject['content'];
		$o = tooltips($o);
		break;
	default :
		return;
		break;
}
