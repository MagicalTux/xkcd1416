<?php
// Sorry for doing that to your server
// but I needed to know

// start point
$start = 'turtles';

$obtained = [];
do_fetch($start, $obtained);

function do_fetch($turtle, &$obtained) {
	echo "Getting $turtle\n";

	// already got that file? no? good, download & crop it
	if (!file_exists($turtle.'.png')) {
		$img_url = 'http://imgs.xkcd.com/turtledown/'.$turtle.'-tiled.png';

		file_put_contents(basename($img_url), get_url($img_url));
		untile(basename($img_url), $turtle.'.png');
	}

	$obtained[$turtle] = true;

	// let's get one level lower (assuming we will always get the same result for a given id)
	$next_level = json_decode(get_url('http://c.xkcd.com/turtle/'.$turtle), true);

	// black or white we don't care, get it all
	foreach(array_merge($next_level['white'], $next_level['black']) as $tmp)
		if (!isset($obtained[$tmp]))
			do_fetch($tmp, $obtained);
}

function untile($from, $to) {
	// appropriate image width is its height, makes crop easy
	$img = imagecreatefrompng($from);
	// crop image
	$h = imagesy($img);
	$img = imagecrop($img, ['x' => 0, 'y' => 0, 'width' => $h, 'height' => $h]);
	if (!imagepng($img, $to)) return false;
	unlink($from); // don't need you anymore
	return true;
}

function get_url($url) {
	static $ch = null;
	if (is_null($ch)) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch);
	if ($res === false) throw new \Exception('Something went wrong');
	return $res;
}

