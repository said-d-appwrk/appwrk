<?php defined( 'ABSPATH' ) or die( 'This action is not allowed.' ); ?>

<?php if( isset($msg) ) { ?>
<div class="notice notice-warning is-dismissible">
    <p><?php echo __( 'Great job. Your almost done!', TTM_TEXTDOMAIN ); ?> <?php echo __( 'Please add your tawk.to script to get started', TTM_TEXTDOMAIN ); ?>. 
    <?php echo __( 'Also see our', TTM_TEXTDOMAIN ); ?> <a href="?page=ttm-tawkto-manager&tab=help">help</a> <?php echo __( 'for how to do this or', TTM_TEXTDOMAIN ); ?> 
    <a href="?page=ttm-tawkto-manager&tab=script"><?php echo __( 'add your script', TTM_TEXTDOMAIN ); ?></a>.</p>
</div>
<?php } ?>

<!-- WordPress 'wrap' container -->
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2><?php echo __( 'Tawk.To Chat WordPress Visibility Options', TTM_TEXTDOMAIN ); ?></h2><br />
    <?php settings_errors(); ?>
    <?php $tab = '&tab='; ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=ttm-<?php echo TTM_TEXTDOMAIN.$tab.'frontend'; ?>" class="nav-tab <?php echo $active_tab == 'frontend' ? 'nav-tab-active' : ''; ?>">
            <?php echo __( 'Website / Blog', TTM_TEXTDOMAIN ); ?> <?php echo __( 'Options', TTM_TEXTDOMAIN ); ?>
        </a>
    <?php if($ttm_options['ttm_advanced_mode']) { ?>
        <a href="?page=ttm-<?php echo TTM_TEXTDOMAIN.$tab.'backend'; ?>" class="nav-tab <?php echo $active_tab == 'backend' ? 'nav-tab-active' : ''; ?>">
            WordPress Dashboard <?php echo __( 'Options', TTM_TEXTDOMAIN ); ?>
        </a>
    <?php } ?>
    <?php if( ttm_woocommerce_active() ) { ?>       
        <a href="?page=ttm-<?php echo TTM_TEXTDOMAIN.$tab.'woocommerce'; ?>" class="nav-tab <?php echo $active_tab == 'woocommerce' ? 'nav-tab-active' : ''; ?>">
            <?php echo __( 'WooCommerce Page Options', TTM_TEXTDOMAIN ); ?>
        </a>
    <?php } ?>
        <a href="?page=ttm-<?php echo TTM_TEXTDOMAIN.$tab.'script'; ?>" class="nav-tab <?php echo $active_tab == 'script' ? 'nav-tab-active' : ''; ?>">
            Tawk.To Script
        </a>
        <a href="?page=ttm-<?php echo TTM_TEXTDOMAIN.$tab.'settings'; ?>" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
            <?php echo __( 'Settings', TTM_TEXTDOMAIN ); ?>
        </a>
        <a href="?page=ttm-<?php echo TTM_TEXTDOMAIN.$tab.'help'; ?>" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">
            <?php echo __( 'Help', TTM_TEXTDOMAIN ); ?>
        </a>        
    </h2>
    <p>
    <?php if(isset($active_tab) && $active_tab != '') $tab = '&tab='.$active_tab; ?>
    <form method="post" action="<?php echo get_admin_url().'admin.php?page=ttm-'.TTM_TEXTDOMAIN.$tab; ?>">
        <?php 
            echo $viewContent;
        ?>
    </p>
   
</div><!-- /.wrap -->




