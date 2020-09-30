# Isaiah Explained

### Platform

isaiahexplained.com is a MySQL and PHP/JQuery driven website using the [Laravel 4.2.x](https://laravel.com/docs/4.2) MVC framework and the [mustache](https://mustache.github.io/) template engine. Site development uses a git flow loosely following the [nvie branching strategy guide](http://nvie.com/posts/a-successful-git-branching-model/).

The site source contains git hooks for BitBucket. When pushing commits to the development branch, the dev site should be updated automatically. When the development branch is merged into the master branch, the www site should be updated automatically. Configuration on both servers is required to support this, and is beyond the scope of this readme.

The [www](www.isaiahexplained.com) production site is currently hosted on a BlueHost server, which significantly impacts its performance. To alleviate performance deficiencies, the [flatten](https://github.com/Anahkiasen/flatten) library is used to flatten PHP pages into flat html. The [dev](dev.isaiahexplained.com) is currently hosted on a DigitalOcean website, along with some store files.