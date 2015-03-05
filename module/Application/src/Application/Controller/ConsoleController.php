<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Application\Entity\Sample as SampleEntity;
use Application\Document\Sample as SampleDocument;

/**
 * Console controller
 *
 * @category    Application
 * @package     Controller
 */
class ConsoleController extends AbstractConsoleController
{
    /**
     * Cron script action template
     */
    public function cronAction()
    {
        // Ensure there is only one cron script running at a time.
        $fpSingleton = fopen(__FILE__, "r") or die("Could not open " . __FILE__);
        if (!flock($fpSingleton, LOCK_EX | LOCK_NB)) {
            fclose($fpSingleton);
            return "Another cron job is running" . PHP_EOL;
        }

        // Cron job here
    }

    /**
     * Populate the database
     */
    public function populateDbAction()
    {
        $sl = $this->getServiceLocator();
        $mm = $sl->get('ModuleManager');

        if ($mm->getModule('DoctrineORMModule')) {
            $em = $sl->get('Doctrine\ORM\EntityManager');

            $repo = $em->getRepository('Application\Entity\Sample');
            $repo->removeAll();

            $dt = new \DateTime();
            for ($i = 1; $i <= 10; $i++) {
                $dt->add(new \DateInterval('PT10S'));

                $entity = new SampleEntity();
                $entity->setValueString("string $i");
                if ($i != 3) {
                    $entity->setValueInteger($i * $i * 100);
                    $entity->setValueFloat($i / 100);
                    $entity->setValueBoolean($i % 2 == 0);
                    $entity->setValueDatetime(clone $dt);
                }
                $em->persist($entity);
            }
            $em->flush();
        }

        if ($mm->getModule('DoctrineMongoODMModule')) {
            $dm = $sl->get('doctrine.documentmanager.odm_default');

            $repo = $dm->getRepository('Application\Document\Sample');
            $repo->removeAll();

            $dt = new \DateTime();
            for ($i = 1; $i <= 10; $i++) {
                $dt->add(new \DateInterval('PT10S'));

                $document = new SampleDocument();
                $document->setValueString("string $i");
                if ($i != 3) {
                    $document->setValueInteger($i * $i * 100);
                    $document->setValueFloat($i / 100);
                    $document->setValueBoolean($i % 2 == 0);
                    $document->setValueDatetime(clone $dt);
                }
                $dm->persist($document);
            }
            $dm->flush();
        }
    }
} 
