Developers Tools
================

We use Composer to manage PHP components and Bower for JS components. With the help of Grunt all front-end components are compiled and minified into **vendor.js** and **vendor.css**.

A number of scripts are written:

* **scripts/install-dependencies**

  This script will install *development* or *production* environment dependencies.

* **scripts/build-front**

  This will concatenate and minify all the 3rd-party JS and CSS into **js/vendor.min.js** and **css/vendor.min.css**.

* **scripts/dev-server**

  This will launch PHP-builtin web-server on port 8000. Do not use in production!

* **scripts/test-backend**

  Run unit tests for PHP code

You should run these scripts from root directory of your project:

```shell
> cd MyProject
> ./scripts/dev-server
```

Adding new front-end dependency
-------------------------------
When adding new dependency simply add the appropriate line to "require" section of **bower.json** and run **scripts/install-dependecies**.

Now add the dependency's .js and .css files to Gruntfile.js lists and run **scripts/build-server** script to create new **vendor.js** and **vendor.css** files.

Put vendor.\* files under git control so you will have them ready to use in production (you compile them in development environment only).
