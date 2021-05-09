<?php 
if(!defined('ABSPATH')) wp_die('Cannot access this file directly.');

class Field{
    protected $name;
    protected $display = 'Field'; 
    protected $value = ''; 

    public function __construct($name, $display, $value){
        $this->name = $name;
        $this->display = $display; 
        $this->value = $value;
    }

    public function draw_text_field(){
        $this->draw_input_field('text');
    }

    public function draw_number_field(){
        $this->draw_input_field('number');
    }

    public function draw_checkbox(){
        ?>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->name ?>" name="<?php echo $this->name; ?>"<?php checked( $this->value ); ?>/>
			<label for="<?php echo $this->name; ?>"><?php _e( $this->display ); ?></label>
        </p>
        <?php

    }

    public function draw_dropdown($options){
        ?>
        <p>
            <label for="<?php echo $this->name; ?>"><?php _e( $this->display ); ?></label>
            <select class="widefat" name="<?php echo $this->name; ?>">
            <?php 
                foreach($options as $value=>$display){
                    ?>
                        <option value = "<?php echo $value;?>" <?php echo ($this->value == $value) ? 'selected': '';?>><?php echo $display; ?></option>

                    <?php
                }
            
            ?>
            </select>
        </p>
        <?php
    }

    function draw_input_field($type){
        ?>
        <p>
			<label for="<?php echo $this->name; ?>"><?php _e( $this->display ); ?></label>
			<input class="widefat" id="<?php echo $this->name; ?>" name="<?php echo $this->name; ?>" type="<?php echo $type; ?>" value="<?php echo $this->value; ?>"/>
		</p>

        <?php
    }
}