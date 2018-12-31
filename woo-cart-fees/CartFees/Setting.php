<?php 

namespace CartFees;

class Setting {
    /**
     * The section in the WooCommerce settings page to add the setting.
     * e.g. Products, General, Shipping etc.
     */
    protected $section;

    /**
     * The data to create the settings tab.
     */
    protected $settings = array();

    /**
     * The required fields when creating a settings tab. 
     * These are validated against inputs.
     */
    protected $requiredSettings = array('id', 'name');

    /**
     * The setting fields to be applied.
     */
    protected $fields = array();

    /**
     * Type of options.
     * Done for lazy evaluation.
     */
    const OPTIONS_ALL_PRODUCTS = 'fetchAllProducts';

    /**
     * All of the constants that can map their values 
     * to a method on the class;
     */
    protected $mapableOptions = array(
        self::OPTIONS_ALL_PRODUCTS,
    );    

    /**
     * Build class.
     */
    public function __construct($section, array $settings) {
        $this->section = $section;
       
        $this->validateSettings($settings);

        $this->settings = $settings;
    }

    public function get($key, $default = null) {
        $key = $this->prefixInPlace($key) ? $key : $this->addPrefix($key);

        $field = $this->getFieldBy('id', $key);
        
        return get_option($key) ?: isset($field['default']) ? $field['default'] : $default;
    }

    public function getFieldBy($fieldKey = 'id', $value) {
        return array_filter($this->fields, function($field, $fieldKey) {
            $field[$fieldKey] == $key;
        });
    }

    /**
     * Adds a title and desription.
     */
    public function addTitleAndDescription($title, $description) {
        $this->fields[] = array(
            'type' => 'title', 
            'name' => self::wrapTextDomain($title),
            'desc' => self::wrapTextDomain($description),
            'id' => $this->settings['id'],         
        );

        return $this;
    }

    /**
     * Adds a generic field. Prefixes the id.
     * Checks if the prefix is already applied first.
     */
    public function addField(array $field) {
        if ( isset($field['id']) and ! $this->prefixInPlace($field['id']) ) {
            $field['id'] = $this->addPrefix($field['id']);
        }
        
        $this->fields[] = $field;

        return $this;
    }  

    /**
     * Shorthand to wrap string in text domain function.
     */
    public static function wrapTextDomain($input) {
        return __($input, WCI_TEXT_DOMAIN);
    }
    
    /**
     * Add the field ending declaration.
     */
    protected function addFieldEnd() {
        $this->fields[] = array(
            'type' => 'sectionend', 
            'id' => $this->settings['id'],
        );
    }    

    /**
     * Create the setting with it's fields.
     */
    public function create() {
        add_filter( "woocommerce_get_sections_{$this->section}", function($sections) {
            $sections[$this->settings['id']] = self::wrapTextDomain($this->settings['name']);
            return $sections;
        });

        if ( ! $this->fields ) {
            return;
        }

        add_filter( "woocommerce_get_settings_{$this->section}", function( $settings, $current_section) {
            if ( $current_section !== $this->settings['id'] ) {
                return $settings;
            }

            $this->addFieldEnd();

            return array_map(function($field) {
                if ( ! isset($field['options']) ) {
                    return $field;
                 }

                 if ( ! in_array($field['options'], $this->mapableOptions) ) {
                    return $field;
                }

                $field['options'] = $this->{$field['options']}();
                
                return $field;
            }, $this->fields);
        }, 10, 2 );
    } 

    /**
     * Fetch all products for option mapping.
     */
    protected function fetchAllProducts() {
        $products = wc_get_products(array(
            'limit' => -1,
            'status' => 'publish',
            'orderby' => 'post_title',
            'order' => 'ASC',
        ));

        $mapped = array();

        foreach($products as $product) {
            $mapped[$product->get_id()] = "{$product->get_title()}. ID:{$product->get_id()}";
        }

        return $mapped;
    }

    /**
     * Validates the setting arguments.
     */
    protected function validateSettings(array $settings) {
        foreach($this->requiredSettings as $key) {
            if ( ! isset($settings[$key]) ) {
                wp_die("WooCommerce Cart Fees Setting: {$key} is required.");
            }   
        }           
    }

    /**
     * Check if the id prefix has been added.
     */
    protected function prefixInPlace($key) {
        return (bool) stristr($key, $this->settings['id']);
    }

    /**
     * Add the prefix.
     */
    protected function addPrefix($key) {
        return "{$this->settings['id']}_{$key}";
    }    
}