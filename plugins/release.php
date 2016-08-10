#!/usr/bin/php
<?php
$mode = $argv[1];
if (!$mode) {
	echo "Preparing archive\n";
	exec("zip -r dog.zip dog dogx");
	echo "Archive ready\n";
	exit;
}
$version_file = 'dog/style.css';
$contents = file_get_contents($version_file);
if (!preg_match('/Version: (.*)/', $contents, $matches)) {
	die("Version string not found\n");
}
$version = trim($matches[1]);
if (!$version) {
	die("Version number not found\n");
}
echo "Current version is: {$version}\n";
$parts = explode('.', $version);
if (strcmp($mode, 'M') === 0) {
	echo "Preparing major release\n";
	$index = 0;
} else if (strcmp($mode, 'm') === 0) {
	echo "Preparing minor release\n";
	$index = 1;
} else if (strcmp($mode, 'r') === 0) {
	echo "Preparing revision release\n";
	$index = 2;
} else {
	die("Invalid release mode {$mode}\n");
}
$val = (int) $parts[$index];
$val++;
$parts[$index] = $val;
for ($i = $index + 1; $i <= count($parts) - 1; $i++) {
	$parts[$i] = 0;
}
$new_version = implode('.', $parts);
$new_contents = str_replace("Version: {$version}", "Version: {$new_version}", $contents);
file_put_contents($version_file, $new_contents);
echo "File updated with version {$new_version}\n";
echo "Preparing archive\n";
exec("zip -r dog.{$new_version}.zip dog");
echo "Version: {$new_version} is ready\n";