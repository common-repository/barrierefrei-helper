<?php
/*
Plugin Name: Barrierefrei Helper
Description: Ein Plugin zur Verbesserung der Barrierefreiheit auf Client-Seiten.
Version: 1.2.2
Author: hyperhex
Author URI: https://hyperhex.de
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: barrierefrei-helper
*/

function barrierefrei_helper_plugin_menu() {
    add_menu_page(__('Barrierefrei Helper', 'barrierefrei-helper'), __('Barrierefreiheit', 'barrierefrei-helper'), 'manage_options', 'barrierefrei-helper', 'barrierefrei_helper_plugin_settings_page', 'dashicons-admin-generic');
}
add_action('admin_menu', 'barrierefrei_helper_plugin_menu');

function barrierefrei_helper_plugin_settings_page() {
    if (isset($_POST['submit_settings'])) {
        check_admin_referer('barrierefrei_helper_settings_update', 'barrierefrei_helper_settings_update_nonce');

        update_option('barrierefrei_helper_font_size', isset($_POST['font_size']) ? '1' : '0');
        update_option('barrierefrei_helper_color_choice', isset($_POST['color_choice']) ? '1' : '0');
        update_option('barrierefrei_helper_contrast', isset($_POST['contrast']) ? '1' : '0');
        update_option('barrierefrei_helper_toc_enabled', isset($_POST['toc_enabled']) ? '1' : '0');
        update_option('barrierefrei_helper_toc_tag', sanitize_text_field($_POST['toc_tag']));
        add_settings_error('barrierefrei_helper_settings', 'barrierefrei_helper_settings_message', __('Einstellungen erfolgreich gespeichert.', 'barrierefrei-helper'), 'updated');
    }
    if (isset($_POST['clear_cache'])) {
        check_admin_referer('barrierefrei_helper_clear_cache', 'barrierefrei_helper_clear_cache_nonce');
        update_option('barrierefrei_helper_last_cache_clear', time());
        add_settings_error('barrierefrei_helper_settings', 'barrierefrei_helper_cache_clear_message', __('Cache erfolgreich geleert.', 'barriereffi-helper'), 'updated');
    } 
    settings_errors('barrierefrei_helper_settings');
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('Einstellungen Barrierefrei Helper', 'barrierefrei-helper'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('barrierefrei_helper_settings_update', 'barrierefrei_helper_settings_update_nonce'); ?>
            <input type="hidden" name="submit_settings" value="1">
            <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php esc_html_e('Schriftgrößenanpassung aktivieren:', 'barrierefrei-helper'); ?></th>
                <td>
                    <input type="checkbox" name="font_size" value="1" <?php checked(1, get_option('barrierefrei_helper_font_size'), true); ?> />
                    <p class="description"><?php esc_html_e('Hinweis: Wenn aktiviert, können Benutzer die Schriftgröße der Startseite anpassen.', 'barrierefrei-helper'); ?></p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php esc_html_e('Farbauswahl aktivieren:', 'barrierefrei-helper'); ?></th>
                <td>
                    <input type="checkbox" name="color_choice" value="1" <?php checked(1, get_option('barrierefrei_helper_color_choice'), true); ?> />
                    <p class="description"><?php esc_html_e('Hinweis: Funktioniert nur, wenn globale Farben über Elementor verwendet werden.', 'barrierefrei-helper'); ?></p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php esc_html_e('Hohen Kontrast Modus aktivieren:', 'barrierefrei-helper'); ?></th>
                <td>
                    <input type="checkbox" name="contrast" value="1" <?php checked(1, get_option('barrierefrei_helper_contrast'), true); ?> />
                    <p class="description"><?php esc_html_e('Hinweis: Wenn aktiviert, können Benutzer zwischen einem hohen Kontrastmodus und Normal umschalten.', 'barrierefrei-helper'); ?></p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php esc_html_e('Inhaltsverzeichnis aktivieren:', 'barrierefrei-helper'); ?></th>
                <td>
                    <input type="checkbox" name="toc_enabled" value="1" <?php checked(1, get_option('barrierefrei_helper_toc_enabled', '0'), true); ?> />
                    <select name="toc_tag">
                        <option value="h1" <?php selected('h1', get_option('barrierefrei_helper_toc_tag')); ?>><?php esc_html_e('h1', 'barrierefrei-helper'); ?></option>
                        <option value="h2" <?php selected('h2', get_option('barrierefrei_helper_toc_tag')); ?>><?php esc_html_e('h2', 'barrierefrei-helper'); ?></option>
                        <option value="h3" <?php selected('h3', get_option('barrierefrei_helper_toc_tag')); ?>><?php esc_html_e('h3', 'barrierefrei-helper'); ?></option>
                        <option value="h4" <?php selected('h4', get_option('barrierefrei_helper_toc_tag')); ?>><?php esc_html_e('h4', 'barrierefrei-helper'); ?></option>
                        <option value="h5" <?php selected('h5', get_option('barrierefrei_helper_toc_tag')); ?>><?php esc_html_e('h5', 'barrierefrei-helper'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Wählen Sie den HTML-Tag aus, der für Hauptüberschriften verwendet wird, damit das Inhaltsverzeichnis ordnungsgemäß funktioniert.', 'barrierefrei-helper'); ?></p>
                </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <form method="post">
            <?php wp_nonce_field('barrierefrei_helper_clear_cache', 'barrierefrei_helper_clear_cache_nonce'); ?>
            <?php submit_button(__('Cache leeren', 'barrierefrei-helper'), 'primary', 'clear_cache'); ?>
        </form>
    </div>
    <?php
}

add_filter('plugin_action_links_barrierefrei-helper/barrierefrei-helper.php', 'barrierefrei_helper_settings_link');
function barrierefrei_helper_settings_link($links) {
    // Build and escape the URL.
    $url = esc_url(add_query_arg(
        'page',
        'barrierefrei-helper',
        get_admin_url() . 'admin.php'
    ));
    // Create the link.
    $settings_link = "<a href='$url'>" . esc_html__('Einstellungen', 'barrierefrei-helper') . '</a>';
    // Adds the link to the end of the array.
    array_push($links, $settings_link);
    return $links;
}

function barrierefreiheit_plugin_scripts() {
    wp_enqueue_script('barrierefreiheit-js', plugins_url('/js/barrierefreiheit.js', __FILE__), array('jquery'), '1.0.0', true);
    
    $settings = array(
        'fontSizeEnabled' => get_option('barrierefrei_helper_font_size'),
        'colorChoiceEnabled' => get_option('barrierefrei_helper_color_choice'),
        'tocEnabled' => get_option('barrierefrei_helper_toc_enabled'),
        'tocTag' => get_option('barrierefrei_helper_toc_tag'),
        'contrastEnabled' => get_option('barrierefrei_helper_contrast'),
        'lastCacheClear' => get_option('barrierefrei_helper_last_cache_clear', 0)
    );

    wp_localize_script('barrierefreiheit-js', 'BarrierefreiheitSettings', $settings);
    wp_enqueue_style('barrierefreiheit-css', plugins_url('/css/barrierefreiheit.css', __FILE__), array(), '1');
}
add_action('wp_enqueue_scripts', 'barrierefreiheit_plugin_scripts');

function barrierefrei_helper_plugin_register_settings() {
    register_setting('barrierefrei_helper-settings-group', 'barrierefrei_helper_font_size');
    register_setting('barrierefrei_helper-settings-group', 'barrierefrei_helper_color_choice');
    register_setting('barrierefrei_helper-settings-group', 'barrierefrei_helper_toc_enabled');
    register_setting('barrierefrei_helper-settings-group', 'barrierefrei_helper_toc_tag');
    register_setting('barrierefrei_helper-settings-group', 'barrierefrei_helper_contrast');
}

add_action('admin_init', 'barrierefrei_helper_plugin_register_settings');


function barrierefreiheit_add_footer_content() {
    $toc_enabled = get_option('barrierefrei_helper_toc_enabled');
    $font_size_enabled = get_option('barrierefrei_helper_font_size');
    $color_choice_enabled = get_option('barrierefrei_helper_color_choice');
    $high_contrast_enabled = get_option('barrierefrei_helper_contrast');
    ?>
    <div id="barrierefreiheit-icon">
        <img src="<?php echo esc_url(plugins_url('/images/barrierefreiheit-icon.png', __FILE__)); ?>" alt="Barrierefreie Einstellungen">
    </div>

    <div id="barrierefreiheit-popup">
        <button id="close-popup">×</button>
        <p>Barrierefreiheit Einstellungen</p>
        <hr>
        <?php if ($color_choice_enabled == '1') : ?>
            <div id="get_colors"></div>
            <div class="head-color">
                <p>Farbe</p>
                <div class="head-color-div">
                    <div class="color-divs"> 
                        <label>Primär <input type="color" id="primary-color" value="#007bff"></label>
                        <button class="color-btn" onclick="resetColor('primary')"><img src="<?php echo esc_url(plugins_url('/images/reset.svg', __FILE__)); ?>" alt="Primäre Farbe zurücksetzen"></button>
                    </div>
                    <div class="color-divs"> 
                        <label>Sekundär <input type="color" id="secondary-color" value="#6c757d"></label>
                        <button class="color-btn" onclick="resetColor('secondary')"><img src="<?php echo esc_url(plugins_url('/images/reset.svg', __FILE__)); ?>" alt="Sekundäre Farbe zurücksetzen"></button>
                    </div>
                    <div class="color-divs"> 
                        <label>Akzent <input type="color" id="accent-color" value="#17a2b8"></label>
                        <button class="color-btn" onclick="resetColor('accent')"><img src="<?php echo esc_url(plugins_url('/images/reset.svg', __FILE__)); ?>" alt="Akzent Farbe zurücksetzen"></button>
                    </div>
                    <div class="color-divs"> 
                        <label>Text <input type="color" id="text-color" value="#343a40"></label>
                        <button class="color-btn" onclick="resetColor('text')"><img src="<?php echo esc_url(plugins_url('/images/reset.svg', __FILE__)); ?>" alt="Text Farbe zurücksetzen"></button>
                    </div>
                </div>
            </div>
            <hr>
        <?php endif; ?>
        
        <?php if ($font_size_enabled == '1') : ?>
            <div class="head-font">
                <p>Schriftgröße</p>
                <div class="head-font-div">
                    <button class="font-btn" id="increase-font-size">+</button>
                    <button class="font-btn" id="decrease-font-size">-</button>
                    <button class="color-btn" onclick="resetFontSize()"><img src="<?php echo esc_url(plugins_url('/images/reset.svg', __FILE__)); ?>" alt="Schriftgröße zurücksetzen"></button>
                </div>
            </div>
            <hr>
        <?php endif; ?>
        
            <div class="head-etc">
                <p>Sonstiges</p>
                <div class="head-etc-div">
                    <?php if ($high_contrast_enabled == '1') : ?>
                        <div class="etc-div">
                            <label for="contrast-toggle">High Contrast Mode</label>
                            <label class="switch">
                            <input style="display: none;" type="checkbox" id="contrast-toggle" onchange="toggleContrastMode()">
                            <span class="slider round"></span>
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="etc-div">
                        <label>Alles zurücksetzen</label>
                        <button style="align-self: end;" class="color-btn" onclick="resetAll()"><img src="<?php echo esc_url(plugins_url('/images/reset.svg', __FILE__)); ?>" alt="Alles zurücksetzen"></button>
                    </div>
                </div>
            </div>
        
        <?php if ($toc_enabled == '1') : ?>
            <hr>
            <div class="head-toc">
                <p>Inhaltsverzeichnis</p>
                <ul id="tocList"></ul>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
add_action('wp_footer', 'barrierefreiheit_add_footer_content');