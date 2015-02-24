DynamicTable demo page (ZF2)
============================

This is DynamicTable project demo application.

Installation
------------

```shell
> git clone https://github.com/basarevych/dynamic-table
> cd dynamic-table
> checkout demo-zf2
> ./scripts/install
```

Web server:

```
<VirtualHost *:80>
    ServerName my-project.example.com
    DocumentRoot /path/to/MyProject/public.prod
    <Directory /path/to/MyProject/public.prod>
        DirectoryIndex index.php
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
