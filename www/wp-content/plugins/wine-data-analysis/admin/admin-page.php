<?php
// Recupere as configurações da tabela personalizada
global $wpdb;
$table_name = $wpdb->prefix . 'winetech_config';

$settings = $wpdb->get_row("SELECT * FROM $table_name");

$thresholdmax = $thresholdmin = $recipients = $tankes = '';
if ($settings) {
    $thresholdmax = $settings->thresholdmax;
    $thresholdmin = $settings->thresholdmin;
    $recipients = unserialize($settings->recipients);
    $tankes = unserialize($settings->tank);
}

// Função para verificar se um usuário está na lista de destinatários
function is_user_selected($user_id, $selected_recipients) {
    // Verifique se $selected_recipients está definido e não vazio
    if (isset($selected_recipients) && is_array($selected_recipients) && in_array($user_id, $selected_recipients)) {
        return true;
    }
    return false;
}
// Função para verificar o tank relacionado
function is_tank_selected($post_id, $tanked_recipients) {
    // Verifique se $tanked_recipients está definido e não vazio
    if (isset($tanked_recipients) && is_array($tanked_recipients) && in_array($post_id, $tanked_recipients)) {
        return true;
    }
    return false;
}


// Função para obter os dados da API
function get_api_data($url) {
    $response = wp_remote_get($url);
    if (is_array($response) && !is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        return json_decode($body);
    }
    return false;
}

// Função para obter os últimos valores com base na data
function get_lastest_values($data) {
    // Ordene os dados com base na data (assumindo que $data é uma array de objetos)
    usort($data, function ($a, $b) {
        return strtotime($a->dateTime) - strtotime($b->dateTime);
    });

    // Pegue os últimos valores
    return array_slice($data, -10);
}

// Função para obter o último valor com base na data
function get_last_value($data) {
    usort($data, function ($a, $b) {
        return strtotime($a->dateTime) - strtotime($b->dateTime);
    });

    // Pegue o último valor
    return array_slice($data, -1);
}

function format_date($date) {
    // Converte a data para um objeto DateTime
    $datetime = new DateTime($date);
    
    // Subtrai um dia
    $datetime->modify('-1 day');
    
    // Formata a data no formato desejado
    return $datetime->format('d/m - H:i:s');
}


// Obtenha os dados da API
$temp_data = get_api_data('https://app-tcc-winetech-dd2c345ee059.herokuapp.com/mgwine/Temp');
$qtd_data = get_api_data('https://app-tcc-winetech-dd2c345ee059.herokuapp.com/mgwine/Qtd');

// Refatore os dados para o formato adequado (temperatura)
$temp_values = [];
$temp_labels = [];
foreach (get_lastest_values($temp_data) as $data) {
    $temp_values[] = (float) $data->temp;
    $temp_labels[] = $data->dateTime;
}

// Refatore os dados para o formato adequado (quantidade)
$qtd_values = [];
$qtd_labels = [];
foreach (get_last_value($qtd_data) as $data) {
    $qtd = (float) $data->qtd;
    // Verifica se o valor é menor que 0 e retorne 0 nesse caso
    $qtd = max($qtd, 0);
    $qtd_values[] = $qtd;
    $qtd_labels[] = $data->dateTime;
}
?>

<div class="wrap">
    <h1>Winetech Data Analysis</h1>
    <hr>
</div>

<div class="container">
    <form method="post" id="plugin-settings-form">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Gráfico de Temperatura (Celsius)</h2>
                        <div class="chart-container">
                            <canvas id="temperature-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Gráfico de Quantidade (litros)</h2>
                        <div class="chart-container">
                            <canvas id="quantity-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                    <h2 class="card-title">Configurações</h2>
                        <div class="form-group">
                            <label for="temperature-thresholdmax">Limite máximo de Temperatura (°C):</label>
                            <input type="number" class="form-control" id="temperature-thresholdmax" name="temperature-thresholdmax" value="<?php echo esc_attr($thresholdmax); ?>">
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="temperature-thresholdmin">Limite mínimo de Temperatura (°C):</label>
                            <input type="number" class="form-control" id="temperature-thresholdmin" name="temperature-thresholdmin" value="<?php echo esc_attr($thresholdmin); ?>">
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="notification-recipients">Usuários para Notificação:</label>
                            <select class="form-control select2" id="notification-recipients" name="notification-recipients[]" multiple>
                                <?php
                                $users = get_users();
                                foreach ($users as $user) {
                                    $selected = is_user_selected($user->ID, $recipients) ? 'selected' : '';
                                    echo '<option value="' . $user->ID . '" ' . $selected . '>' . $user->user_login . ' (' . $user->user_email . ')</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="relational-tank">Tanques para Notificação:</label>
                            <select class="form-control select2" id="relational-tank" name="relational-tank[]" multiple>
                                <?php
                                $tanks = get_posts(array('post_type' => 'tanque', 'posts_per_page' => -1));
                                foreach ($tanks as $tank) {
                                    $selectedtank = is_tank_selected($tank->ID, $tankes) ? 'selected' : '';
                                    echo '<option value="' . $tank->ID . '" ' . $selectedtank . '>' . $tank->post_title . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <br>
                        <div class="form-group text-right">
                            <button class="btn btn-primary" type="submit" name="save-settings">Salvar Configurações</button>
                        </div>
                        <div id="settings-message"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- <meta http-equiv="refresh" content="5"> -->


<hr>

<script>
    // Constroi o gráfico de temperatura
    var temperatureChartCanvas = document.getElementById('temperature-chart').getContext('2d');
    var temperatureChartData = {
        labels: <?php echo json_encode(array_map('format_date', $temp_labels)); ?>,
        datasets: [{
            label: 'Temperatura (°C)',
            data: <?php echo json_encode($temp_values); ?>,
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2
        }]
    };
    var temperatureChart = new Chart(temperatureChartCanvas, {
        type: 'line',
        data: temperatureChartData,
    });

    // Constroi o gráfico de quantidade
    var quantityChartCanvas = document.getElementById('quantity-chart').getContext('2d');
    var quantityChartData = {
        labels: <?php echo json_encode(array_map('format_date', $qtd_labels)); ?>,
        datasets: [{
            label: 'Quantidade (litros)',
            data: <?php echo json_encode($qtd_values); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2
        }]
    };
    var quantityChart = new Chart(quantityChartCanvas, {
        type: 'bar',
        data: quantityChartData,
    });
</script>
