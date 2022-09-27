<div class="fdestadisticas-setup-instructions">
	<p><?php esc_html_e( 'Set up your FD Estadisticas account to enable spam filtering on this site.', 'fdestadisticas' ); ?></p>
	<?php FDEstadisticas::view( 'get', array( 'text' => __( 'Set up your FD account' , 'fdestadisticas' ), 'classes' => array( 'fdestadisticas-button', 'fdestadisticas-is-primary' ) ) ); ?>
</div>
