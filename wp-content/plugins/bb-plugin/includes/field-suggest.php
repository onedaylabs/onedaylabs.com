<?php

$class 			= isset( $field['class'] ) ? ' ' . $field['class'] : '';
$action 		= isset( $field['action'] ) ? $field['action'] : '';
$data   		= isset( $field['data'] ) ? $field['data'] : '';
$placeholder 	= isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_attr( 'Start typing...', 'fl-builder' );
$limit 			= isset( $field['limit'] ) ? $field['limit'] : 'false';
$value  		= FLBuilderAutoSuggest::get_value( $action, $value, $data );

?>
<input type="text" class="text text-full fl-suggest-field<?php echo $class; ?>" name="<?php echo $name; ?>" data-value='<?php echo $value; ?>' data-action="<?php echo $action; ?>" data-action-data="<?php echo $data; ?>" data-limit="<?php echo $limit; ?>" placeholder="<?php echo $placeholder; ?>" />