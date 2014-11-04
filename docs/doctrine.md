ZF2 & Doctrine
==============

This is most widely used setup for ZF2 + Doctrine using doctrine/doctrine-orm-module.

Entity manager is available as 'Doctrine\ORM\EntityManager' service.

You start using Doctrine by setting up the driver and credentials in **config/autoload/local.php** configuration file.

Directories
-----------
* **database/**

  This directory contains database schema

* **module/Application/src/Application/Entity**

  This directory is for Doctrine entities

* **module/Application/src/Application/Repository**

  And this one for Doctrine repositories

* **module/Application/src/Application/Doctrine**

  Our extensions to Doctrine directory

Initial skeleton includes Sample entity and a repository for it. Delete it when you create your schema and entities.

Getting Started
---------------
I personally use [Database-first](http://doctrine-orm.readthedocs.org/en/latest/tutorials/getting-started-database.html) approach when developing for Doctrine.

This means that I handcraft database schema for a specific DB server first. Then create entities and repositories by hand also.

**The steps when using Database-first approach**:

1. Create the actual database on server (empty): See [README](../database/README.md) for server-specific console commands.

  Set this database driver and credentials in **local.php**.

2. Edit **database/mysql.schema.sql** (we will be using MySQL)

  ```sql
  DROP TABLE IF EXISTS `sample`;

  CREATE TABLE `sample` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `value_string` varchar(255) NULL,
    `value_integer` int NULL,
    `value_float` float NULL,
    `value_boolean` tinyint(1) NULL,
    `value_datetime` datetime NULL,
    CONSTRAINT `sample_pk` PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
  ```

3. Import the schema

  ```shell
  > php public/index.php dbal:import database/mysql.schema.sql
  ```

  This will obviously (re)create the tables so all the data (if you had any) is lost.

4. Create the entities

  ```php
  <?php

  namespace Application\Entity;

  use Doctrine\ORM\Mapping as ORM;

  /**
   * Sample entity
   * 
   * @ORM\Entity(repositoryClass="Application\Repository\Sample")
   * @ORM\Table(name="sample")
   */
  class Sample
  {
    /**
     * Row ID
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * String value
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value_string;

    /**
     * Integer value
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $value_integer;

    /**
     * Float value
     *
     * @ORM\Column(type="float", nullable=true)
     */
    protected $value_float;

    /**
     * Boolean value
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $value_boolean;

    /**
     * DateTime value
     *
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $value_datetime;
  }
  ```

  As you can see we created all the table fields as protected properties

5. Auto generate getters/setters for our fields

  Parse entities:
  ```shell
  > php public/index.php orm:generate-entities module/Application/src
  ```

  Optionaly remove backups that have been just created:
  ```shell
  > rm module/Application/src/Application/Entity/*~
  ```

  This will make sure all the getters/setters are created for all the entities. See the actual file for the resulting entity.

Caching
-------
When you enable **memcached.local.php** Doctrine cache is setup to use Memcached for all its internal stuff. It's up to you when to enable *result* cache.

Consider the following example:

```php
<?php

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class Sample extends EntityRepository
{
    public function findAllCached()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('s')
           ->from('Application\Entity\Sample', 's');

        $query = $qb->getQuery();
        $query->useResultCache(true);  // <== This is it
        $result = $query->getResult();

        return $result;
    }
}
```

You use it as any other repository method. In controller, for example:
```php
    $sl = $this->getServiceLocator();
    $em = $sl->get('Doctrine\ORM\EntityManager');

    $repo = $em->getRepository('Application\Entity\Sample');
    $all = $repo->findAllCached();
```

No need to modify this code for enabled or disabled Memcached cases. If no cache is enabled this method will just work as non-cached version.

UTC DateTime
------------
One of the problems with ORM is datetime field. Not all the DBs have means to store timezone with the datetime values. And your server (or even several servers) could be in any timezone possible.

There is a simple solution for all the cases: a field that automatically converts datetime to UTC when writing to the DB and back to PHP server timezone when retrieving values.

**module/Application/src/Application/Doctrine/UtcDateTime.php** implements this solution. Everything is already included in the configs so you can just set field type to "utcdatetime" instead of "datetime" in order to use it.
