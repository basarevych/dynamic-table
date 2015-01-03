<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Example\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;

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
     * Constructor
     *
     * @param  null|int|string  $name    Optional name
     * @param  array            $options Optional options
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name ? $name : 'edit-sample', $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

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

        $datetime = new Element\Text('datetime');
        $datetime->setLabel('DateTime');
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
        $csrf->setRequired(true);
        $filter->add($csrf);

        $string = new Input('string');
        $string->setRequired(true)
               ->getFilterChain()
               ->attach(new Filter\StringTrim());
        $filter->add($string);

        $integer = new Input('integer');
        $integer->setRequired(false)
                ->getFilterChain()
                ->attach(new Filter\StringTrim());
        $filter->add($integer);

        $float = new Input('float');
        $float->setRequired(false)
              ->getFilterChain()
              ->attach(new Filter\StringTrim());
        $filter->add($float);

        $boolean = new Input('boolean');
        $boolean->setRequired(true);
        $filter->add($boolean);

        $datetime = new Input('datetime');
        $datetime->setRequired(false)
                 ->getFilterChain()
                 ->attach(new Filter\StringTrim());
        $filter->add($datetime);

        $this->inputFilter = $filter;
        return $filter;
    }
}
