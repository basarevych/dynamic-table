<?php

namespace ApplicationTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Form\Confirm as ConfirmForm;

class ConfirmTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testInvalidConfirmForm()
    {
        $form = new ConfirmForm();

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('id')->getMessages()), "ID should have errors");
    }

    public function testValidConfirmForm()
    {
        $form = new ConfirmForm();

        $input = [
            'security' => $form->get('security')->getValue(),
            'id' => 42,
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
    }
}
