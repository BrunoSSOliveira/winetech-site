<?php
/*
Plugin Name: Winetech Data Analysis
Description: Um plugin para conectar ao MongoDB e exibir dados de monitoramento.
Version: 1.0
Author: winetech: Bruno Sousa, Lucas Costa, Otávio Codato, Pedro Santos.
*/

// Função para criar a tabela personalizada
function create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'winetech_config';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        thresholdmax FLOAT,
        thresholdmin FLOAT,
        recipients TEXT,
        tank TEXT,
        PRIMARY KEY (id),
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Registra a função para ser executada quando o plugin for ativado
register_activation_hook(__FILE__, 'create_custom_table');

// Página de configurações do plugin
function winetech_config_page() {
    include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');
}

function winetech_menu() {
    add_menu_page('Winetech', 'Winetech', 'manage_options', 'winetech-monitoring', 'winetech_config_page', 'dashicons-welcome-widgets-menus', 2);
}

add_action('admin_menu', 'winetech_menu');

function save_settings() {
    global $wpdb;

    if (isset($_POST['save-settings'])) {
        // Obtém os valores dos campos do formulário
        $thresholdmax = sanitize_text_field($_POST['temperature-thresholdmax']);
        $thresholdmin = sanitize_text_field($_POST['temperature-thresholdmin']);
        $recipients = isset($_POST['notification-recipients']) ? $_POST['notification-recipients'] : array();
        $tank = isset($_POST['relational-tank']) ? $_POST['relational-tank'] : array();

        // Serializa os destinatários para salvar no banco de dados
        $recipients = serialize($recipients);
        $tank = serialize($tank);

        // Verifica se já existem configurações na tabela
        $existing_settings = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}winetech_config");

        if ($existing_settings) {
            // Atualiza as configurações se já existirem
            $wpdb->update(
                "{$wpdb->prefix}winetech_config",
                array(
                    'thresholdmax' => $thresholdmax,
                    'thresholdmin' => $thresholdmin,
                    'recipients' => $recipients,
                    'tank' => $tank,
                ),
                array('id' => $existing_settings->id)
            );
        } else {
            // Insere novas configurações se não existirem
            $wpdb->insert(
                "{$wpdb->prefix}winetech_config",
                array(
                    'thresholdmax' => $thresholdmax,
                    'thresholdmin' => $thresholdmin,
                    'recipients' => $recipients,
                    'tank' => $tank,
                )
            );
        }
    }
}

add_action('admin_init', 'save_settings');

// Adiciona a biblioteca Chart.js
function add_chart_js() {
    wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js', array(), '2.9.4');
}

add_action('admin_enqueue_scripts', 'add_chart_js');

// Adiciona a biblioteca Bootstrap
function enqueue_bootstrap_css() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap/dist/css/bootstrap.min.css');
}

add_action('wp_enqueue_scripts', 'enqueue_bootstrap_css');

// Adiciona a biblioteca Jquery
function enqueue_jquery() {
    wp_deregister_script('jquery'); // Desregistrar a versão padrão do jQuery
    wp_register_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0', true);
    wp_enqueue_script('jquery');
}

add_action('wp_enqueue_scripts', 'enqueue_jquery');

