#! /usr/bin/php
<?PHP

function echo_r($a) {
    echo $a;
    echo "\n";
    return $a;
}

function filter_noempty($a) {
    $b = trim($a);
    return ! empty($b);
}

function newest($a, $b) {
    return filectime($b) - filectime($a);
}

$roots = explode("\n", shell_exec('ls ' . ($base = '/srv/www/vhosts/production/')));
foreach (array_filter($roots, 'filter_noempty') as $dir) {
    $dir = trim($dir);
    
    // Geolive data is generally stored in a database
    // Except for user files. if something bad happens to Geolive it is usually pretty
    // easy to fix except for restoring user files.
    
    // the intentention is to backup the content every once in a while and then if something happens
    // it can be restored from the backup. the intention is to keep a rolling copy of the content
    // and if something happens it will hopefully stop backing up. eg if I delete the users_files folder that
    // will fail the check below
    
    if (file_exists($folder = $base . $dir . '/http/components/com_geolive/users_files')) {
        echo 'Geolive Spice - User Content: ' . $folder . "\n";
        
        chdir(dirname($folder));
        
        // echo shell_exec(echo_r('cd '.dirname($folder)))."\n";
        echo ($file = 'geolive_users_files_' . date('Y-M-D H:i') . '.zip') . "\n";
        shell_exec(echo_r('zip -r -p \'' . $file . '\' users_files')) . "\n";
        echo shell_exec(echo_r('mv \'' . $file . '\' ' . $base . $dir . '/')) . "\n";
        
        $roll = array_filter(
            explode("\n", shell_exec('find ' . $base . $dir . '/ -maxdepth 1 -name \'geolive_users_files*\'')), 
            'filter_noempty');
        usort($roll, 'newest');
        if (count($roll) > 2) {
            foreach (array_slice($roll, 2) as $old) {
                echo shell_exec(echo_r('rm \'' . $old . '\' -r -f'));
            }
        }
        // print_r($roll);
        // die();
    } else {
        // echo 'no '.$folder;
    }
    echo "\n";
}
//echo shell_exddec('find /srv/www/vhost/production/ -iwholename \'http/components/com_geolive\'');

