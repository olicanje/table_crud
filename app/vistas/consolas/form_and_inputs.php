
<form method='post' name='<?php echo \core\Array_Datos::contenido("form_name", $datos); ?>' action="?menu=<?php echo $datos['controlador_clase']; ?>&submenu=validar_<?php echo $datos['controlador_metodo']; ?>" >
	
	<?php echo \core\HTML_Tag::form_registrar($datos["form_name"], "post"); ?>
	
	<input id='id' name='id' type='hidden' value='<?php echo \core\Array_Datos::values('id', $datos); ?>' />
        
	Nombre: <input id='nombre' name='nombre' type='text' size='100'  maxlength='100' value='<?php echo \core\Array_Datos::values('nombre', $datos); ?>'/>
	<?php echo \core\HTML_Tag::span_error('nombre', $datos); ?>
        <br />
        
        Fecha Lanzamiento: <input id='fecha_lanzamiento' name='fecha_lanzamiento' type='text' size='89'  maxlength='100' value='<?php echo \core\Array_Datos::values('fecha_lanzamiento', $datos); ?>'/>
	<?php echo \core\HTML_Tag::span_error('fecha_lanzamiento', $datos); ?>
        <br />
        
        Precio: <input id='precio' name='precio' type='text' size='102'  maxlength='100' value='<?php echo \core\Array_Datos::values('precio', $datos); ?>'/>
	<?php echo \core\HTML_Tag::span_error('precio', $datos); ?>
        <br />
        
        Unidades Stock: <input id='unidades_stock' name='unidades_stock' type='text' size='92'  maxlength='100' value='<?php echo \core\Array_Datos::values('unidades_stock', $datos); ?>'/>
	<?php echo \core\HTML_Tag::span_error('unidades_stock', $datos); ?>
        <br />       
        
	Descripcion:<br />
	<textarea id='descripcion' name='descripcion' type='textarea' cols='100'  rows='10' ><?php echo \core\Array_Datos::values('descripcion', $datos); ?></textarea>
	<?php echo \core\HTML_Tag::span_error('descripcion', $datos); ?>
	<br />
        
	<?php echo \core\HTML_Tag::span_error('errores_validacion', $datos); ?>
	
	<input type='submit' value='Enviar'>
	<input type='reset' value='Limpiar'>
	<button type='button' onclick='location.assign("?menu=<?php echo $datos['controlador_clase']; ?>&submenu=index");'>Cancelar</button>
</form>
