<?php
/*
"{"repository": {"website": "", "fork": false, "name": "IsaiahExplained", "scm": "git", "owner": "thetekton", "absolute_url": "/thetekton/isaiahexplained/", "slug": "isaiahexplained", "is_private": true}, "truncated": false, "commits": [{"node": "b93b3bbebe82", "files": [{"type": "modified", "file": "app/controllers/ResourceController.php"}], "raw_author": "Terrance Wood <tj@ixqus.com>", "utctimestamp": "2015-04-10 05:48:04+00:00", "author": "thetekton", "timestamp": "2015-04-10 07:48:04", "raw_node": "b93b3bbebe8251899e73ef3fb0a2a9e63544d15b", "parents": ["3700749139f9"], "branch": "develop", "message": "Testing POST deployment hook with dev.isaiahexplained.com.\n", "revision": null, "size": -1}], "canon_url": "https://bitbucket.org", "user": "thetekton"}"
*/
$deploy_log_file = 'deploy.log';
$update_log_file = 'update.log';
/*
Uncomment the line below to log the payload received (presumeably from BitBucket...):
*/
//file_put_contents($deploy_log_file, serialize($_POST['payload']), FILE_APPEND);
$home_dir = '/home1/isaiahde';
$repo_dir = $home_dir . '/isaiahexplained.git';
$web_root_dir = $home_dir . '/public_html/isaiahexplained';

// Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
$bin_path = '/usr/bin/';
$git_bin_path = $bin_path . 'git';
$cli_path = $bin_path . 'php-cli';
$composer_path = $cli_path . ' ' . $home_dir . '/composer.phar';

$update = false;

// Parse data from Bitbucket hook payload
$payload = json_decode($_POST['payload']);

if (empty($payload->commits)){
    // When merging and pushing to bitbucket, the commits array will be empty.
    // In this case there is no way to know what branch was pushed to, so we will do an update.
    $update = true;
} else {
    foreach ($payload->commits as $commit) {
        $branch = $commit->branch;
        if ($branch === 'master' || isset($commit->branches) && in_array('master', $commit->branches)) {
            $update = true;
            break;
        }
    }
}

if ($update) {
    // Do a git checkout to the web root
    $fetch_cmd = 'cd ' . $repo_dir . ' && ' . $git_bin_path  . ' fetch';
    exec($fetch_cmd);
    $checkout_cmd = 'cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $web_root_dir . ' ' . $git_bin_path  . ' checkout -f master';
    exec($checkout_cmd);

    // Log the deployment
    $get_rev_cmd = 'cd ' . $repo_dir . ' && ' . $git_bin_path  . ' rev-parse --short HEAD';
    $commit_hash = shell_exec($get_rev_cmd);
    $deploy_date = date('m/d/Y h:i:s a');
    file_put_contents($deploy_log_file, $deploy_date . " Deployed branch: " .  $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);
    $site_update_cmd = 'cd ' . $home_dir . ' && ' . $composer_bin_path . ' update';
    $update_result = shell_exec($site_update_cmd);
    file_put_contents($update_log_file, $deploy_date . " Updated www: " . $update_result . "\n", FILE_APPEND);
    $clear_site_cache_cmd = 'rm -rf ' . $web_root_dir . '/app/storage/cache/*';
}
$clear_cache_result = shell_exec($clear_site_cache_cmd);
file_put_contents($update_log_file, $deploy_date . " Cleared www cache: " . $clear_site_cache_cmd . "\n", FILE_APPEND);
?>