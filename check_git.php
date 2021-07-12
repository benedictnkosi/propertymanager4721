<?php

if (command_exist('git --version')) {
    echo 'git is installed';
} else {
    echo 'git is installed';
}

function command_exist($cmd) {
    $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
    print_r( $return);
    return !empty($return);
}

?>

