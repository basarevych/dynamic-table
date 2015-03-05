<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace ExampleORM\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;
use Zend\Validator;
use Doctrine\ORM\EntityManager;
use Application\Validator\EntityNotExists;
use Application\Validator\Integer;
use Application\Validator\Float;
use Application\Filter\LocaleFormattedNumber;

/**
 * Create/Edit Sample entity form
 *
 * @category    Admin
 * @package     Form
 */
class EditSampleForm extends Form
{
    /**
     * The input filter
     *
     * @var InputFilter
     */
    protected $inputFilter = null;

    /**
     * Doctrine EntityManager
     *
     * @var EntityManager
     */
    protected $em = null;

    /**
     * ID of the entity being edited (null when creating)
     *
     * @var integer
     */
    protected $id = null;

    /**
     * Constructor
     *
     * @param EntityManager    $em          Doctrine EntityManager
     * @param integer          $id          ID of the entity being edited (null when creating)
     * @param null|int|string  $name        Optional name
     * @param array            $options     Optional options
     */
    public function __construct($em, $id = null, $name = 'edit-sample', $options = array())
    {
        $this->em = $em;
        $this->id = $id;

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        if ($this->id) {
            $id = new Element\Hidden('id');
            $this->add($id);
        }

        $string = new Element\Text('string');
        $string->setLabel('String');
        $this->add($string);

        $integer = new Element\Text('integer');
        $integer->setLabel('Integer');
        $this->add($integer);

        $float = new Element\Text('float');
        $float->setLabel('Float');
        $this->add($float);

        $boolean = new Element\Radio('boolean');
        $boolean->setLabel('Boolean')
                ->setValueOptions([ -1 => 'Not set', 0 => 'False', 1 => 'True' ])
                ->setValue(-1);
        $this->add($boolean);

        $datetime = new Element\DateTime('datetime');
        $datetime->setLabel('DateTime');
        $datetime->setFormat("Y-m-d H:i:s P");
        $datetime->setAttribute('step', 'any');
        $this->add($datetime);
    }

    /**
     * Retrieve input filter used by this form
     *
     * @return null|InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->inputFilter)
            return $this->inputFilter;

        $filter = new InputFilter();

        $csrf = new Input('security');
        $csrf->setRequired(true)
             ->setBreakOnFailure(false);
        $filter->add($csrf);

        if ($this->id) {
            $id = new Input('id');
            $id->setRequired(true)
               ->setBreakOnFailure(false);
            $filter->add($id);
        }

        $params = [
            'entityManager' => $this->em,
            'entity'        => 'Application\Entity\Sample',
            'property'      => 'value_string',
        ];
        if ($this->id)
            $params['ignoreId'] = $this->id;

        $string = new Input('string');
        $string->setRequired(true)
               ->setBreakOnFailure(false)
               ->getFilterChain()
               ->attach(new Filter\StringTrim());
        $string->getValidatorChain()
               ->attach(new EntityNotExists($params));
        $filter->add($string);

        $integer = new Input('integer');
        $integer->setRequired(false)
                ->setBreakOnFailure(false)
                ->getFilterChain()
                ->attach(new Filter\StringTrim())
                ->attach(new LocaleFormattedNumber());
        $integer->getValidatorChain()
                ->attach(new Integer());
        $filter->add($integer);

        $float = new Input('float');
        $float->setRequired(false)
              ->setBreakOnFailure(false)
              ->getFilterChain()
              ->attach(new Filter\StringTrim())
              ->attach(new LocaleFormattedNumber());
        $float->getValidatorChain()
               ->attach(new Float());
        $filter->add($float);

        $boolean = new Input('boolean');
        $boolean->setRequired(true)
                ->setBreakOnFailure(false);
        $filter->add($boolean);

        $params = [
            'format' => $this->get('datetime')->getFormat()
        ];

        $datetime = new Input('datetime');
        $datetime->setRequired(false)
                 ->setBreakOnFailure(false)
                 ->getValidatorChain()
                 ->attach(new Validator\Date($params));
        $filter->add($datetime);

        $this->inputFilter = $filter;
        return $filter;
    }
}
