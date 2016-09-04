#!/usr/bin/php
<?php
$file = basename(__FILE__);
$path = trim($argv[1], DIRECTORY_SEPARATOR);
if (!$path) {
	echo "Please specify release source path\n";
	echo "\tex: $ php {$file} themes/dog\n";
	exit;
}
$mode = $argv[2];
if (!$mode) {
	echo "Please specify release mode (M, m, r)\n";
	echo "\tex: $ php {$file} themes/dog r\n";
	exit;
}
$full_path = realpath($path);
$name = basename($full_path);
$version_file = is_file("{$full_path}/style.css") ? "{$full_path}/style.css" : "{$full_path}/plugin.php";
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
$old_archive_name = "{$name}.{$version}.zip";
$archive_name = "{$name}.{$new_version}.zip";
echo "Preparing archive: {$archive_name}\n";
chdir($full_path . '/..');
exec("zip -r {$archive_name} {$name}");
$old_dest = "{$path}/{$old_archive_name}";
$dest = "{$path}/{$archive_name}";
echo "Establishing FTP connection\n";
$conn_id = ftp_connect('ftp.dorinoanagurau.ro');
$login_result = ftp_login($conn_id, 'wp@public.dorinoanagurau.ro', 'cb3!c)#~VT]-');
echo "Deleting archive: {$old_dest}\n";
ftp_delete($conn_id, $old_dest);
echo "Uploading archive: {$dest}\n";
if (!ftp_put($conn_id, $dest, $archive_name, FTP_BINARY)) {
	die('Unable to upload archive');
}
ftp_close($conn_id);
echo "Deleting local archive: {$archive_name}\n";
@unlink($archive_name);
echo "Version: {$new_version} is ready\n";