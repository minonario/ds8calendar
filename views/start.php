<?php

//phpcs:disable VariableAnalysis
// There are "undefined" variables here because they're defined in the code that includes this file as a template.
//
?>
<div id="ds8calendars-plugin-container">

	<div class="ds8calendars-lower">

		<div class="ds8calendars-boxes">
                   
                  <div class="wrap">

                    <h2><?php _e('Finanzas Digital - Tablas (Shortcode)') ?></h2>

                    <form class="ds8-form" method="post" action="options.php">
                    <?php settings_fields('ds8-settings-group'); ?>
                    <?php do_settings_sections('ds8-settings-page') ?>

                        <table class="form-table">
                        <?php DS8Calendar_Admin::create_form($options); ?>
                          
                        </table>

                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        </p>

                    </form>
                  </div>
                  
		</div>
	</div>
</div>