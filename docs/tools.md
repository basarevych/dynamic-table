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

You should run these scripts from root directory of your project, like this:

```shell
> cd MyProject
> ./scripts/dev-server
```

Read [installation/suggested workflow](workflow.md) doc on how to use these scripts.
