<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace ExampleODM\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Exception\NotFoundException;
use Application\Document\Sample as SampleDocument;
use Application\Form\Confirm as ConfirmForm;
use ExampleODM\Form\EditSample as EditSampleForm;

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
        $dm = $sl->get('doctrine.documentmanager.odm_default');

        $documents = $dm->getRepository('Application\Document\Sample')
                        ->findAll();

        return new ViewModel([
            'documents'  => $documents
        ]);
    }

    /**
     * Create/edit entity form action
     */
    public function editFormAction()
    {
        $sl = $this->getServiceLocator();
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        // Handle validate request
        if ($this->params()->fromPost('query') == 'validate') {
            $field = $this->params()->fromPost('field');
            $data = $this->params()->fromPost('form');

            $form = new EditSampleForm($dm, @$data['id']);
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

        $document = null;
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if ($id) {
            $document = $dm->getRepository('Application\Document\Sample')
                           ->find($id);
            if (!$document)
                throw new NotFoundException('Wrong ID');
        }

        $script = null;
        $form = new EditSampleForm($dm, $id);
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

                if (!$document)
                    $document = new SampleDocument();

                $document->setValueString($data['string']);
                $document->setValueInteger(empty($data['integer']) ? null : $data['integer']);
                $document->setValueFloat(empty($data['float']) ? null : $data['float']);
                $document->setValueBoolean($data['boolean'] == -1 ? null : $data['boolean']);
                $document->setValueDatetime($date);

                $dm->persist($document);
                $dm->flush();

                $script = "$('#modal-form').modal('hide'); window.location.reload()";
            }
        } else if ($document) {       // Load initial form values
            $boolean = -1;
            if (($b = $document->getValueBoolean()) !== null)
                $boolean = $b ? 1 : 0;

            $datetime = "";
            if (($dt = $document->getValueDatetime()) !== null) {
                $format = $form->get('datetime')->getFormat();
                $datetime = $dt->format($format);
            }

            $form->setData([
                'id'        => $document->getId(),
                'string'    => $document->getValueString(),
                'integer'   => \Application\Tool\Number::localeFormat($document->getValueInteger()),
                'float'     => \Application\Tool\Number::localeFormat($document->getValueFloat()),
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
        $dm = $sl->get('doctrine.documentmanager.odm_default');

        $document = null;
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');

        $document = $dm->getRepository('Application\Document\Sample')
                       ->find($id);
        if (!$document)
            throw new NotFoundException('Wrong ID');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {  // Handle form submission
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $dm->remove($document);
                $dm->flush();

                $script = "$('#modal-form').modal('hide'); window.location.reload()";
            }
        } else if ($document) {       // Load initial form values
            $form->setData([ 'id' => $document->getId() ]);
        }

        $model = new ViewModel([
            'name'      => $document->getValueString(),
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
