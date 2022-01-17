
<h1> <?php esc_html_e( 'Expense Manager Settings', EXP_MAN_TXT_DOMAIN ); ?> </h1>
<hr>
<?php settings_errors(); ?>

<form method="POST" action="options.php">
    <?php
    settings_fields( "exp_man_settings_group" );
    do_settings_sections( $this->page );
    submit_button();
    ?>
</form>