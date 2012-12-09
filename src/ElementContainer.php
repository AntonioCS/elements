<?php

namespace Elements;

/**
* For elements which are containers (div, spam, fieldset, etc)
*/
class ElementContainer extends element {


    /**
    * This will hold the elements
    *
    * @var array
    */
    protected $_elements = array();

    /**
    * This will hold a reference to the position of the element in the elements array (_elements),
    * by usin
    *   id_element => ref
    *
    * @var array
    */
    protected $_elements_ref = array();
    
    /**
     * This will be the same as elements_ref but with the actual reference of the object (using spl_object_hash)
     * @var array     
     */
    protected $_elements_ref_obj = array();


    /*
     inject(&$array,$position,$value) {

        if ($position == 0)
            $array = array_merge(array($value),$array);
        elseif (!isset($array[$position]))
            $array[$position] = $value;
        else {
            $part1 = array_slice($array,0,$position);
            $part2 = array_merge(array($value),array_slice($array,$position));

            $array = array_merge($part1,$part2);
        }
    }
     */
    /**
     * 
     * @param \Elements\Element $element
     */
    protected function getElementRef(\Elements\Element $element) {
        return spl_object_hash($element);        
    }


    /**
    * To add html directly
    *
    * @param string $element
    *
    * @deprecated Just use the add() method
    */
    public function addHtml($html) {
        $e = new Element;
        $e->setRenderCode((string)$html);
        
        $this->add($e);
    }      
  
    /**
    * Add element to the container
    *
    * @param mixed $element Can either be pure html, element or element_container
    */
    public function add(\Elements\Element $element, $positonToAdd = null) {


        $ref = null;
        if (is_object($element)) { //assume element descendent
            if (in_array($element,$this->_elements)) {
                return;
            }
                  
            //check to see if has an id or name and use it for reference in the elements
            $ref = $element->getAttribute('id') ?: $element->getAttribute('name'); //PHP 5.3

            $element->setParent($this);
        }

        //DONE: Create an array for the references, so that the _elements array will be a numeric array and not an associative
        if ($positonToAdd !== null) {
            helper_array::inject($this->_elements,$positonToAdd,$element);
        }
        else {
            $this->_elements[] = $element;
        }
        
        if ($ref) {
            if ($positonToAdd !== null)
                $this->_elements_ref[$ref] = $positonToAdd;
            else
                $this->_elements_ref[$ref] = array_pop(array_keys($this->_elements));
        }

        //Since this is an object and it's done by reference I can just return the reference to the object or code in case of it no being an object
        return $element;
    }

    /**
    * Wrapper for the add() method so that it can return the instance of the class and not the element that was added (useful in certain cases)
    *
    * @param mixed $element
    */
    public function addThis($element) {
        if ($this->add($element)) {
            return $this;
        }
        
        return null;
    }

    /**
    * Add element before 'element'
    *
    * @param string $element_ref
    * @param mixed $element
    *
    * @return mixed
    */
    public function addBefore($element_ref, $element) {
        if (isset($this->_elements_ref[$element_ref])) {
            //Since I am using the helper_array::inject I don't have to decrease the value of _elements_ref. The inject method will push the other element down
            return $this->add($element,$this->_elements_ref[$element_ref]);
        }

        return null;
    }

    /**
    * Add after element
    *
    * @param mixed $element_ref
    * @param mixed $element
    * @return mixed
    */
    public function addAfter($element_ref, $element) {
        if (isset($this->_elements_ref[$element_ref])) {
            $pos = $this->_elements_ref[$element_ref] +1;
            return $this->add($element,$pos);
        }

        return null;
    }

    /**
    * Add element to the top position
    *
    * @param mixed $element
    * @return mixed
    */
    public function addTop($element) {
        return $this->add($element,0);
    }

    public function __get($name) {
        return $this->getElement($name) ?: parent::__get($name); //PHP 5.3
    }

    /**
    * Return reference of element
    *
    * @param string $ref
    */
    public function getElement($ref) {
        if (isset($this->_elements_ref[$ref])) {
            return $this->_elements[$this->_elements_ref[$ref]];
        }
        return null;
    }

	
    /**
    * Process the elements
    *
    * @param array $data
    * @return array
    */
    public function beforeRender($data = array()) {
        return array_merge(
            array(
                'elements' => implode(
								"\n",
								($this->_process_type == 0 ?
									$this->processElementsNoWrap() :
									$this->processElementsWrap($container_name)
								)
							)
            ),
            $data
        );
    }
}
