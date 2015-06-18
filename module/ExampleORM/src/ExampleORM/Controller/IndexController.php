<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace ExampleORM\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Exception\NotFoundException;
use Application\Entity\Sample as SampleEntity;
use Application\Form\Confirm as ConfirmForm;
use ExampleORM\Form\EditSample as EditSampleForm;

/**
 * Index controller
 *
 * @category    Example
 * @package     Controller
 */
class IndexController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $entities = $em->getRepository('Application\Entity\Sample')
                       ->findAll();

        return new ViewModel([
            'entities'  => $entities
        ]);
    }

    /**
     * Create/edit entity form action
     */
    public function editFormAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        // Handle validate request
        if ($this->params()->fromPost('query') == 'validate') {
            $field = $this->params()->fromPost('field');
            $data = $this->params()->fromPost('form');

            $form = new EditSampleForm($em, @$data['id']);
            $form->setData($data);
            $form->isValid();

            $control = $form->get($field);
            $messages = [];
            foreach ($control->getMessages() as $msg)
                $messages[] = $translate($msg);

            return new JsonModel([
                'valid'     => (count($messages) == 0),
                'messages'  => $messages,
            ]);
        }

        $entity = null;
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if ($id) {
            $entity = $em->getRepository('Application\Entity\Sample')
                         ->find($id);
            if (!$entity)
                throw new NotFoundException('Wrong ID');
        }

        $script = null;
        $form = new EditSampleForm($em, $id);
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {  // Handle form submission
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $date = null;
                if (!empty($data['datetime'])) {
                    $format = $form->get('datetime')->getFormat();
                    $date = \DateTime::createFromFormat($format, $data['datetime']);
                }

                if (!$entity)
                    $entity = new SampleEntity();

                $entity->setValueString($data['string']);
                $entity->setValueInteger(empty($data['integer']) ? null : $data['integer']);
                $entity->setValueFloat(empty($data['float']) ? null : $data['float']);
                $entity->setValueBoolean($data['boolean'] == -1 ? null : $data['boolean']);
                $entity->setValueDatetime($date);

                $em->persist($entity);
                $em->flush();

                $script = "$('#modal-form').modal('hide'); window.location.reload()";
            }
        } else if ($entity) {       // Load initial form values
            $boolean = -1;
            if (($b = $entity->getValueBoolean()) !== null)
                $boolean = $b ? 1 : 0;

            $datetime = "";
            if (($dt = $entity->getValueDatetime()) !== null) {
                $format = $form->get('datetime')->getFormat();
                $datetime = $dt->format($format);
            }

            $form->setData([
                'id'        => $entity->getId(),
                'string'    => $entity->getValueString(),
                'integer'   => \Application\Tool\Number::localeFormat($entity->getValueInteger()),
                'float'     => \Application\Tool\Number::localeFormat($entity->getValueFloat()),
                'boolean'   => $boolean,
                'datetime'  => $datetime
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Delete entity form action
     */
    public function deleteFormAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $entity = null;
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');

        $entity = $em->getRepository('Application\Entity\Sample')
                     ->find($id);
        if (!$entity)
            throw new NotFoundException('Wrong ID');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {  // Handle form submission
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->remove($entity);
                $em->flush();

                $script = "$('#modal-form').modal('hide'); window.location.reload()";
            }
        } else if ($entity) {       // Load initial form values
            $form->setData([ 'id' => $entity->getId() ]);
        }

        $model = new ViewModel([
            'name'      => $entity->getValueString(),
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new \Application\Exception\NotFoundException('Action is not found');
    }
}
