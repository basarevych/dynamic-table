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
use Zend\Validator;

/**
 * Simple form with no elements
 *
 * @category    Admin
 * @package     Form
 */
class NoticeForm extends Form
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
     * @param null|int|string  $name        Optional name
     * @param array            $options     Optional options
     */
    public function __construct($name = 'notice', $options = array())
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);
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

        $this->inputFilter = $filter;
        return $filter;
    }
}
