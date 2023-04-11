<?php

//First array is the changes, second is the upgrade
$versions = require '_versions.php';

array_reverse($versions, true);

$latestVersion = null;
$allChangelog = '== Change Log ==' . PHP_EOL.PHP_EOL;

$counter = 0;

echo '== Change Log ==' . PHP_EOL;
echo PHP_EOL;
foreach ($versions as $version => $data) {
    if(!$latestVersion) {
        $latestVersion = $version;
    }
    $txt = '= '.$version.' ='.PHP_EOL;
    foreach ($data[0] as $change) {
        $txt.= '* ' . $change . PHP_EOL;
    }
    $txt.= PHP_EOL;
    $allChangelog.= $txt;
    if($counter < 3) {
        echo $txt;
    }
    $counter++;
}

$counter = 0;
echo '== Upgrade Notice ==' . PHP_EOL;
echo PHP_EOL;
foreach ($versions as $version => $data) {
    if($counter < 3) {
        echo '= '.$version.' ='.PHP_EOL;
        echo '* ' . $data[1] . PHP_EOL;
        echo PHP_EOL;
    }else {
        break;
    }
    $counter++;
}

file_put_contents('export/files/changelog.txt', $allChangelog);
file_put_contents('.version', 'VERSION='.$latestVersion);