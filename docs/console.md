ZF2 Console Controller
======================

Single console controller is included with this skeleton: **module/Application/src/Application/Controller/ConsoleController.php**

Run php with no arguments to see usage information:

```shell
> php public/index.php
```

Controller has the following commands initially:

* cron

  Many if not all the projects have a need to periodically run a script. You can add this to your root's crontab:

  ```cron
  */3 * * * * sudo -u www php /home/project.root/public/index.php cron
  ```

  "sudo -u www" will run this command as user *www* (replace the name if needed). You might also want to add the following line to **sudoers** to stop sudo from spamming the logs about each script invocation.

  ```
  Defaults syslog_goodpri=debug
  ```

* populate-db

  This command is for filling the empty (just created) database with initial data. Included version has code for creating some number of Sample entities - replace it with your version.

  You use it like this:

  ```shell
  > php public/index.php dbal:import database/mysql.schema.sql
  > php public/index.php populate-db
  ```

Adding a command
----------------
In order to add a command do the following:

1. Add to module's **module.config.php** to 'console/router/routes' section:

  ```php
  'my-command' => [
    'options' => [
      'route'    => 'my-command [-v] <param1> [<param2>]',
      'defaults' => [
        'controller' => 'Application\Controller\Console',
        'action'     => 'my-command'
      ]
    ]
  ],
  ```

  This command accepts optinal **-v** option and param1 and param2 arguments. param2 is also optional.

2. Add to the controller:

  ```php
  public function myCommandAction()
  {
    $request  = $this->getRequest();
    $verbose  = $request->getParam('v');
    $param1   = $request->getParam('param1');
    $param2   = $request->getParam('param2');
  }
  ```

3. Add command usage information to **module/Application/Module::getConsoleUsage()**:

  ```php
  return [
    'my-command [-v] <param1> [<param2>]' => '',
    [ PHP_EOL,      'The long command description goes here' ],
    [ '-v',         'Enable verbose mode' ],
    [ '<param1>',   'Parameter #1 description' ],
    [ '<param2>',   'Parameter #2 description' ],
  ];
  ```

4. Run it like this:

  ```shell
  > php public/index.php my-command -v foo bar
  ```
