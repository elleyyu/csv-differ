<?php
header('Content-type: application/json');

$lists = $_POST['lists'] ?? [];
$requestString = trim($_POST['requestString']) ?? '';
$requestString = str_replace('∩', '&', $requestString);
$requestString = str_replace('∪', '|', $requestString);

if (empty($requestString) || count($lists) == 0) {
	echo json_encode([
		'count' => 0,
		'text' => "",
	]);
	exit;
}
$matchesTimes = 0;
$matchesLists = [];
$operators = [
	'&' => 'array_intersect',
	'-' => 'array_diff',
	'|' => 'array_merge',
];
while( preg_match('/([\$\w\[\]]+)\s*([\&\-\|])\s*(\w+)?/', $requestString, $matches) ) {
	if (strpos($matches[1], '$matchesLists') === false) {
		$matchesLists[$matchesTimes] = "{$operators[$matches[2]]}(\$lists['{$matches[1]}'], \$lists['{$matches[3]}'])";
	} else {
		$matchesLists[$matchesTimes] = "{$operators[$matches[2]]}({$matches[1]}, \$lists['{$matches[3]}'])";
	}
	$requestString = str_replace($matches[0], "\$matchesLists[$matchesTimes]", $requestString);

	$matchesTimes ++;
}

foreach ($matchesLists as $key => $list) {
	$now = count($matchesLists) - $key - 1;
	$requestString = str_replace("\$matchesLists[$now]", $matchesLists[$now], $requestString);
}

foreach ($lists as $key => $list) {
	$lists[$list['name']] = explode(PHP_EOL, $list['value']);
	unset($lists[$key]);
}

eval("\$result = $requestString;");

$result = array_unique($result);
echo json_encode([
	'count' => count($result),
	'text' => implode(PHP_EOL, $result),
]);
exit;

?>
