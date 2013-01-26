<?php

namespace Elements;

/**
* For elements which are containers (div, spam, fieldset, etc)
*/
class ElementContainer extends Element {
        
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

    /**
    * To add html directly
    *
    * @param string $element
    *
    */
    public function addHtml($html) {
        $e = new Element;
        $e->setRenderCode((string)$html);
        
        $this->add($e);
    }     
  
   /**
     * Add element to the container    
     * 
     * @param \Elements\Element $element
     * @param int|null $positon
     * @return \Elements\Element
     */
    public function add(\Elements\Element $element, $position = null) {
        if (in_array($element,$this->_elements)) {                                    
            return;
        }
               
        if ($position !== null && is_numeric($position)) {
            if ($position == 0) {                
                array_unshift($this->_elements, $element);
            }
            elseif (!isset($this->_elements[$position])) {
                $this->_elements[$position] = $element;
            }
            else {
                $part1 = array_slice($this->_elements,0,$position);
                $part2 = array_merge(array($element),array_slice($this->_elements,$position));

                $this->_elements = array_merge($part1,$part2);
            }
        }
        else {            
            $this->_elements[] = $element;
        }
        
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
    * @param \Elements\Element $element
    * @param string $ref   
    *
    * @return mixed
    */
    public function addBefore(\Elements\Element $element, $ref) {
        $pos = $this->find($ref);
        
        if ($pos !== null) {                      
            return $this->add($element,$pos);
        }

        return null;
    }

    /**
    * Add after element
    *
    * @param \Elements\Element $element
    * @param string $ref
    * 
    * @return mixed
    */
    public function addAfter(\Elements\Element $element, $ref) {
        $pos = $this->find($ref);
        if ($pos !== null) {                    
            return $this->add($element,++$pos);
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
	
    /**
    * Process the elements
    *
    * @param array $data
    * @return array
    */
    public function beforeRender($data = array()) {
        $html = array();
        
        foreach ($this->_elements as $element) {
            $html[] = $element->render();
        }                  
        
        return array('elements' => implode("\n",$html));    
    }  
    
    /**
     * jQuery type element search
     * 
     * Start with # for id search or . for class search
     * 
     * @param string $elementRef
     * @return int|null Return int for a valid position or null for invalid
     */
    public function find($elementRef) {
        $sel = $elementRef[0];
        $ref = substr($elementRef, 1);
              
        $position = null;
        
        switch ($sel) {
            case '#':
                $position = $this->findById($ref);
            break;
            case '.':
                $position = $this->findByClass($ref);
            break;
        }
        
        return $position;
    }
    
    public function findById($id) {        
        return $this->findByAttribute('id', $id);
    }
    
    public function findByClass($class) {
        return $this->findByAttribute('class', $class);
    }
    
    /**
     * 
     * @param string $attributeName
     * @param string $attributeValue
     * @return null|int
     */
    public function findByAttribute($attributeName, $attributeValue) {
        $containers = array();
        
        foreach ($this->_elements as $pos => $element) {            
            if ($attributeValue == $element->getAttribute($attributeName)) {
                return $pos;
            }
            elseif ($element instanceof self) {
                $containers[] = $element;
            }
        }     
        
        if (!empty($containers)) {
            foreach ($containers as $container) {
                $pos = $container->findByAttribute($attributeName,$attributeValue);
                
                if ($pos !== null) {
                    return $pos;
                }
            }
        }
        
        return null;
    }
}
