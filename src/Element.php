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
    protected static $_config = array(
        'view' => null,
        'templatesPath' => null
    );
    
    /**
     *
     * @var object
     */
    protected $_view = null;

	/**
	 *
	 * Element Contruct
	 *	 
	 */
    public function __construct($view = null) {
        if ($view) {
            $this->setView($view);
        }		
	}
    
    /**
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
        return $this->_view ?: self::$_config['view'];
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
    * Set or reset an attribute
    *
    * @param string $varname
    * @param string $value
    */
    public function __set($varname,$value) {
        $this->setAttribute($varname, $value);
    }
    /**
     * The __get method might be overwritten. It's better if the code to get the attribute is in another method so that it can be called directly
     *
     * @param string $varname
     */

    public function __get($varname) {
    	return $this->getAttribute($varname);
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
                
        if (!$this->_tplPath)
            throw new NoElementTemplate(__CLASS__);
        
        $view = $this->getView();
        
        if (!$view) {
            throw new NoViewObject(__CLASS__);        
        }
        
        $view->load($this->_template);
        
        $data = $this->beforeRender();
        
        if ($this->hasAttributes()) {
            $view->set('attributes', $this->getAttributes());
        }
        
        if (!empty($data)) {
            $view->set($data);
        }
/*
        $tpl = new acs_view($this->tpl_path,false);

        //$data = $this->beforeHtml(); //must be here to allow the before_html to alter the attributes
        $data = $this->beforeHtml(); //Must change call in other classes before I can change here

        if ($this->hasAttributes())
            $tpl->attributes = $this->getAttributes();

        return $tpl->addData($data)->returnRender();
 * 
 */
    }

    /**
    * To be overloaded in the child class
    * This will be called by the method render()
    * This will be the data set in the view data 
    * 
    *
    * @return array()
    * 	Return an associative array
    *
    */
    protected function beforeRender() {
        return array();
    }
    
    /**
     * Due extra processing on the rendered element template code
     * 
     * @param string $render
     * @return string
     */
    protected function afterRender($render) {
        return $render;        
    }



    public function __toString() {
        return $this->html();
    }
}

class NoElementTemplate extends \Exception {}
class NoViewObject extends \Exception {}