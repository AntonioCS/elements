<?php

namespace Elements;
/**
* Abstract class for elements
*/
class Element {

    /**
    * Element attributes
    *
    * @var array
    */
    protected $_attributes = array();
    
    /**
     * Regarding elements (ex.: textarea) that have a value
     * 
     * @var string
     */
    protected $_value = null;
    
    /**
     * Used when the element is just to return html (or any thing that just doesn't need processing)
     * 
     * @var string
     */
    protected $_renderCode = null;

    /**
    * Parent element
    *
    * @var acs_element_container
    */
    protected $_parent = null;

    /**
    * Path to the template. If relative path the templates path entrie in the _config must be set
    *
    * @var string
    */
    protected $_template = null;
    
    /**
     *
     * @var array
     */
    public static $_config = array(
        'view' => null        
    );
    
    /**
     *
     * @var int
     */
    protected static $_count = 0;


    /**
     *
     * @var object
     */
    protected $_view = null;

    
    /**
     *
     * @var array
     */
    protected $_data = array();      
    
    /**
     * 
     * @param string $template
     * @param objec $view
     */
    public function __construct($template = null,$view = null) {
        if ($template) {
            $this->setTemplate($template);
        }           
        
        if ($view) {
            $this->setView($view);
        }		
        else {
            if (self::$_config['view']) {
                $this->setView(clone self::$_config['view']);
            }            
        }
        
        self::$_count++;        
        $this->setAttribute('id', 'element_' . self::$_count);
	}
    
    /**
     * With this set. The render code method will just return this
     * 
     * @param string $code
     * @return \Elements\Element
     */
    public function setRenderCode($code) {
        $this->_renderCode = $code;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRenderCode() {
        return $this->_renderCode;
    }
    
    /**
     * Set the view object
     * 
     * @param mixed $view
     * @return \Elements\Element
     */
    public function setView($view) {
        $this->_view = $view;
        return $this;
    }
    
    /**
     * Get the view object
     * 
     * @return mixed
     */
    public function getView() {        
        return $this->_view;
    }
    
    /**
     * Set the element template
     * The view object must already have the location of the templates
     * 
     * @param string $template
     * @return \Elements\Element
     */
    public function setTemplate($template) {
        $this->_template = $template;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getTemplate() {
        return $this->_template;
    }

    /**
    * Set the parent of this element
    *
    * @param \Elements\ElementContainer $parent
    */
    public function setParent(ElementContainer $parent) {
        $this->_parent = $parent;
        $parent->add($this);
        return $this;
    }

    /**
    * Retrieve parent of element
    *
    */
    public function getParent() {
        return $this->_parent;
    }

    /**
     * Common setters
     * To bypass the __call method
     */
    public function setValue($value) { return $this->setAttribute('value',$value); }
    public function setType($value) { return $this->setAttribute('type',$value); }
    public function setId($value) { return $this->setAttribute('id',$value); }
    public function setClass($value) { return $this->setAttribute('class',$value); }    
    public function setStyle($value) { return $this->setAttribute('style',$value); }

    /**
    * Append a value to an attribute
    *
    * @param string $name
    * @param string $value
    */
    public function appendAttribute($name, $value) {
        if (isset($this->_attributes[$name]) && $this->_attributes[$name] != '') {
            $this->_attributes[$name] .= ' ' . $value;
        }
        else
            $this->setAttribute($name,$value);

        return $this;
    }

    /**
     * Set an attribute and return class instance
     *
     * @param string $attributename
     * @param string $value
     */
    public function setAttribute($attributename,$value) {
        $this->_attributes[$attributename] = $value;
        return $this;
    }
    
    /**
     * Remove element attribute
     * 
     * @param string $attributename
     * @return boolean|null
     */
    public function removeAttribute($attributename) {
        if (isset($this->_attributes[$attributename])) {
            unset($this->_attributes[$attributename]);
            return true;
        }
        
        return null;
    }

    /**
    * Retrieve the attribute
    *
    * @param string $varname
    */
    public function getAttribute($name) {
        if (isset($this->_attributes[$name]))
            return $this->_attributes[$name];
        return null;
    }

    /**
    * Method to return all attributes in format:
    *  attribute_name = "attribute_value"
    *
    * @return string
    */
    protected function getAttributes() {
        $attributes = array(''); //Add a space just in case there are no attributes
        foreach ($this->_attributes as $attributename => $attributevalue) {
            if ($attributevalue && $attributename[0] != '_') //attributes that start with _ will be used to do other things (like specifing validator, ways to sanitize etc)
                $attributes[] = $attributename . '="' . $attributevalue . '"';
        }
        
        return implode(' ',$attributes);
    }

    /**
    * Method to determine if element has any attributes (will return true if it has)
    *
    */
    public function hasAttributes() {
        return !empty($this->_attributes);
    }

    /**
    * Method to merge an external array of attributes with the attributes of this element
    *
    * @param array $newattributes
    */
    public function mergeAttributes(array $newattributes) {
        $this->_attributes = array_merge($this->_attributes,$newattributes);
    }

    /**
    * Methos to return all the attributes of the element
    *
    */
    public function retrieveAllAttributes() {
        return $this->_attributes;
    }

    /**    
    *
    * @param string $varname
    * @param string $value
    */
    public function __set($varname,$value) {
        $this->setData($varname, $value);
    }
    /**
     * The __get method might be overwritten. It's better if the code to get the attribute is in another method so that it can be called directly
     *
     * @param string $varname
     */

    public function __get($varname) {
    	return $this->getData($varname);
    }

    /**
     * 
     * @param string $dataName
     * @param mixed $dataValue
     * @return \Elements\Element
     */
    public function setData($dataName,$dataValue) {
        $this->_data[$dataName] = $dataValue;
        return $this;
    }
    
    /**
     * 
     * @param string $dataName
     * @return mixed
     */
    public function getData($dataName) {
        if (isset($this->_data[$dataName])) {
            return $this->_data[$dataName];
        }
        
        return null;
    }
    
    /**
     * Return element html
     *
     */
    public function render() {
        
        $renderCode = $this->getRenderCode();
        
        if ($renderCode) {
            return $renderCode;
        }                      
        
        $view = $this->getView();
        
        if (!$view) {
            throw new NoViewObject(get_class($this));        
        }
        
        $view->load($this->_template);
        
        $data = array_merge($this->beforeRender(), $this->_data);
        
        if ($this->hasAttributes()) {
            $view->set('attributes', $this->getAttributes());
        }
        
        if (!empty($data)) {
            $view->set($data);
        }
        
        $render = $this->afterRender($view->render());
        
        return $render;        
    }
    
    /**
     * 
     * @return string
     */
    public function __toString() {
        return $this->render();
    }    

    /**
    * To be overloaded in the child class
    * This will be called by the method render()
    * This will be the data set in the view data 
    *   
    * @return array()
    * 	Return an associative array
    *
    */
    protected function beforeRender() {
        return array();
    }
    
    /**
     * Do extra processing on the rendered element template code
     * 
     * @param string $render
     * @return string
     */
    protected function afterRender($render) {
        return $render;        
    }  
    
    /**
     * Set a custom attribute with an object reference
     * 
     */
    protected function markElement() {
        $this->setAttribute('data-element-ref', spl_object_hash($this));
    }
    
    /**
     * 
     * @return string
     */
    public function getElementMark() {
        return $this->getAttribute('data-element-ref');
    }
}

class NoElementTemplate extends \Exception {}
class NoViewObject extends \Exception {}