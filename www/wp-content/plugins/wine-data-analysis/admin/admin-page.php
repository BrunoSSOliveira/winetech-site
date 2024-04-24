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

     // Verifica se $recipients é um array
     if (is_array($recipients)) {
        foreach ($recipients as $recipient_id) {
            // Use $recipient_id para buscar informações sobre o usuário
            $user = get_user_by('ID', $recipient_id);
            if ($user) {
                $user_id = $user->ID;
                $user_name = $user->user_login;
                $user_role = $user->roles[0];
            }
        }
    }
}
    
$current_user = wp_get_current_user();
if (current_user_can('administrator') || current_user_can($user_role)) {

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
        
        // Formata a data no formato desejado
        return $datetime->format('d/m - H:i:s');
    }

    // The logged-in user is an admin.
    $temp_data = get_api_data('https://app-tcc-winetech-dd2c345ee059.herokuapp.com/mgwine/Temp');
    $qtd_data = get_api_data('https://app-tcc-winetech-dd2c345ee059.herokuapp.com/mgwine/Qtd');
    
    // Refatore os dados para o formato adequado (temperatura)
    $temp_values = [];
    $temp_labels = [];
    foreach (get_lastest_values($temp_data) as $data) {
        // Verifica se o valor da temperatura é diferente de zero antes de adicioná-lo aos dados do gráfico
        if ($data->temp != 0) {
            $temp_values[] = (float) $data->temp;
            $temp_labels[] = date('Y-m-d\TH:i:s.000\Z', strtotime($data->dateTime));
        }
        // $temp_values[] = (float) $data->temp;
        // $temp_labels[] = date('Y-m-d\TH:i:s.000\Z', strtotime($data->dateTime));
    }

    // Check if the last temperature value exceeds the threshold
    $last_temp_value = end($temp_values); // Get the last temperature value
    if ($last_temp_value > $thresholdmax) {
        // Initialize an array to store recipient emails
        $recipient_emails = array();

        // Itera pelos IDs dos destinatários e receber seus e-mails
        foreach ($recipients as $user_id) {
            $user_info = get_userdata($user_id);
            if ($user_info) {
                $user_email = $user_info->user_email;
                $recipient_emails[] = $user_email;
            }
        }

        // Verifica se há e-mails de destinatários
        if (!empty($recipient_emails)) {
            $to = implode(',', $recipient_emails);
            $subject = 'Limite máximo de temperatura excedido';
            $message = 'A temperatura excedeu o limite máximo cadastrado. Temperatura atual: ' . $last_temp_value . '°C';
            $headers = array('Content-Type: text/html');

            $email_sent = wp_mail($to, $subject, $message, $headers);
        }
    } elseif ($last_temp_value < $thresholdmin) {
        // Initialize an array to store recipient emails
        $recipient_emails = array();

        // Itera pelos IDs dos destinatários e receber seus e-mails
        foreach ($recipients as $user_id) {
            $user_info = get_userdata($user_id);
            if ($user_info) {
                $user_email = $user_info->user_email;
                $recipient_emails[] = $user_email;
            }
        }

        // Verifica se há e-mails de destinatários
        if (!empty($recipient_emails)) {
            $to = implode(',', $recipient_emails);
            $subject = 'Limite mínimo de temperatura foi excedido';
            $message = 'A temperatura excedeu o limite mínimo cadastrado. Temperatura atual: ' . $last_temp_value . '°C';
            $headers = array('Content-Type: text/html');

            $email_sent = wp_mail($to, $subject, $message, $headers);
        }

    }

    // Use a função para obter os últimos valores
    $lastest_values = get_lastest_values($temp_data);

    if (!empty($lastest_values)) {
        // Obtenha o valor mais recente
        $last_entry = end($lastest_values);

        // Converta a data e hora da última entrada em um objeto DateTime
        $last_entry_date = new DateTime($last_entry->dateTime);

        // Obtenha a data e hora atual
        $current_date = new DateTime();

        // Calcule a diferença em segundos entre a última data e a data atual
        $time_diff = $current_date->getTimestamp() - $last_entry_date->getTimestamp();
        // echo $time_diff;
        if ($time_diff > 10830) {
            // Initialize an array to store recipient emails
            $recipient_emails = array();

            // Itera pelos IDs dos destinatários e receber seus e-mails
            foreach ($recipients as $user_id) {
                $user_info = get_userdata($user_id);
                if ($user_info) {
                    $user_email = $user_info->user_email;
                    $recipient_emails[] = $user_email;
                }
            }

            // Verifica se há e-mails de destinatários
            if (!empty($recipient_emails)) {
                $to = implode(',', $recipient_emails);
                $subject = 'Sensores sem resposta';
                $message = 'Perda de conexão com os sensores (verifique a conexão com os sensores).';
                $headers = array('Content-Type: text/html');

                $email_sent = wp_mail($to, $subject, $message, $headers);
            }
            $status = "falha";
        } else {
            $status = "conectado";
        }
    }
        

    // Refatore os dados para o formato adequado (quantidade)
    $qtd_values = [];
    $qtd_labels = [];
    foreach (get_last_value($qtd_data) as $data) {
        $qtd = (float) $data->qtd;
        // Verifica se o valor é menor que 0 e retorne 0 nesse caso
        $qtd = max($qtd, 0);
        $qtd_values[] = $qtd;
        $qtd_labels[] = date('Y-m-d\TH:i:s.000\Z', strtotime($data->dateTime));
    }
    ?>

    <div class="wrap">
        <h1>Winetech Data Analysis</h1>
        <hr>
    </div>

    <div class="container">
        <form method="post" id="plugin-settings-form">
            <div class="row">
                <?php if (current_user_can('administrator')) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Contagem de Sensores Cadastrados</h2>
                            <div class="form-group">
                                <label for="selected-month-iot">Selecione o Mês:</label>
                                <input type="month" class="form-control" id="selected-month-iot" name="selected-month-iot">
                            </div>
                            <p id="sensor-count-result">Total de Sensores Cadastrados:</p>
                            <br>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" type="button" id="calculate-sensor-count" style="background-color:rgba(242, 41, 91, 1);
                                    border-radius:4px;
                                    border:1px solid #ffffff;
                                    display:inline-block;
                                    color:#ffffff;
                                    padding:6px 15px;
                                    text-decoration:none;">Total de Sensores</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>  
                    
                <div class="card">
                    <div class="card-body">
                        <div id="status" style="display: flex; align-items: center;">
                        <h4 style="margin-right: 10px;">Status de conexão: </h4>
                        <div id="statusIconContainer" style="flex: none;">
                            <svg id="statusIcon" width="24" height="24"></svg>
                        </div>
                        </div>
                    </div>
                </div>


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
                            <h2 class="card-title">Cálculo da Média de Temperatura</h2>
                            <div class="form-group">
                                <label for="selected-month">Selecione o Mês:</label>
                                <input type="month" class="form-control" id="selected-month" name="selected-month">
                            </div>
                            <p id="average-temperature-result">Média de Temperatura:</p>
                            <br>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" type="button" id="calculate-average" style="background-color:rgba(242, 41, 91, 1);
                                                                                                                    border-radius:4px;
                                                                                                                    border:1px solid #ffffff;
                                                                                                                    display:inline-block;
                                                                                                                    color:#ffffff;
                                                                                                                    padding:6px 15px;
                                                                                                                    text-decoration:none;">Calcular Média de Temperatura</button>
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
            <h2 class="card-title">Configurações de Temperatura</h2>
            <form method="post" id="temperature-settings-form">
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
                <div class="form-group text-right">
                    <button class="btn btn-primary btn-block" type="submit" name="save-temperature-settings" style="background-color:rgba(242, 41, 91, 1);
                                                                                    border-radius:4px;
                                                                                    border:1px solid #ffffff;
                                                                                    display:inline-block;
                                                                                    color:#ffffff;
                                                                                    padding:6px 15px;
                                                                                    text-decoration:none;">Salvar Configurações de Temperatura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (current_user_can('administrator')) { ?>
<div class="col-md-4">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Configurações de Cliente</h2>
            <form method="post" id="notification-settings-form">
                <div class="form-group">
                    <label for="notification-recipients">Usuário responsável pelo sensor:</label>
                    <select class="form-control select2 w-100" id="notification-recipients" name="notification-recipients[]" multiple>
                        <?php
                        $users = get_users();
                        foreach ($users as $user) {
                            $selected = is_user_selected($user->ID, $recipients) ? 'selected' : '';
                            echo '<option value="' . $user->ID . '" ' . $selected . '>' . $user->user_login . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <br>
                <div class="form-group">
                    <label for="relational-tank">Tanque (onde está o sensor):</label>
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
                    <button class="btn btn-primary btn-block" type="submit" name="save-notification-settings" style="background-color:rgba(242, 41, 91, 1);
                                                                                    border-radius:4px;
                                                                                    border:1px solid #ffffff;
                                                                                    display:inline-block;
                                                                                    color:#ffffff;
                                                                                    padding:6px 15px;
                                                                                    text-decoration:none;">Salvar Configurações de Notificação</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

            </div>
        </form>
    </div>

    <meta http-equiv="refresh" content="20">


    <hr>
<?php
} else {
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
                            <h2 class="card-title">O tipo de usuário logado não possui sensores cadastrados</h2>
                            <br>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <br>
    <hr>
    
<?php } ?>

<script>
    // Constroi o gráfico de temperatura
    var temperatureChartCanvas = document.getElementById('temperature-chart').getContext('2d');
    var temperatureChartData = {
        labels: <?php echo json_encode(array_map('format_date', $temp_labels)); ?>,
        datasets: [{
            label: 'Temperatura (°C)',
            data: <?php echo json_encode($temp_values); ?>,
            borderColor: 'rgba(242, 41, 91, 1)',
            borderWidth: 2
        }]
    };
    
    // Verifique o tamanho da tela
    if (window.innerWidth < 768) {
        // Ajuste a largura e altura do gráfico para dispositivos móveis
        temperatureChartCanvas.canvas.style.width = '100%';
        temperatureChartCanvas.canvas.style.height = '250px'; // Ajuste a altura desejada
    }
    var temperatureChart = new Chart(temperatureChartCanvas, {
        type: 'line',
        data: temperatureChartData,
        options: {
            responsive: true, // Torna o gráfico responsivo
            maintainAspectRatio: true // Permite que o gráfico ajuste sua proporção automaticamente
        }
    });

    // Constroi o gráfico de quantidade
    var quantityChartCanvas = document.getElementById('quantity-chart').getContext('2d');
    var quantityChartData = {
        labels: <?php echo json_encode(array_map('format_date', $qtd_labels)); ?>,
        datasets: [{
            label: 'Quantidade (litros)',
            data: <?php echo json_encode($qtd_values); ?>,
            backgroundColor: 'rgba(162, 0, 40, 0.5607843137254902)',
            borderColor: 'rgba(242, 41, 91, 1)',
            borderWidth: 2
        }]
    };
    // Verifique o tamanho da tela
    if (window.innerWidth < 768) {
        // Ajuste a largura e altura do gráfico para dispositivos móveis
        quantityChartCanvas.canvas.style.width = '100%';
        quantityChartCanvas.canvas.style.height = '250px'; // Ajuste a altura desejada
    }
    var quantityChart = new Chart(quantityChartCanvas, {
        type: 'bar',
        data: quantityChartData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
    });
</script>

<script>
    // Inclua os dados de temperatura em uma variável JavaScript
    const tempData = <?php echo json_encode($temp_data); ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
            // Função para calcular a média de temperaturas
            function calculateAverage() {
                // Obtém o valor do mês selecionado no input "selected-month"
                const selectedMonth = document.getElementById('selected-month').value;

                // Filtra os dados de temperatura para incluir apenas os do mês selecionado
                const selectedYearMonth = selectedMonth.slice(0, 7);
                const temperatures = tempData.filter(data => {
                    if (data.dateTime && data.dateTime.length >= 7) {
                        const dataYearMonth = data.dateTime.slice(0, 7);
                        return dataYearMonth === selectedYearMonth;
                    }
                    return false;
                });

                // Verifica se há dados para o mês selecionado
                if (temperatures.length) {
                    // Calcula a média das temperaturas
                    const totalTemperature = temperatures.reduce((acc, data) => acc + data.temp, 0);
                    const averageTemperature = totalTemperature / temperatures.length;

                    // Exibe o resultado
                    const resultElement = document.getElementById('average-temperature-result');
                    resultElement.innerHTML = `Média de Temperatura para ${selectedMonth}: ${averageTemperature.toFixed(2)}°C`;
                } else {
                    // Caso não haja dados para o mês selecionado
                    const resultElement = document.getElementById('average-temperature-result');
                    resultElement.innerHTML = 'Dados indisponíveis para o mês selecionado.';
                }
            }

            // Adicione um evento de clique ao botão "Calcular Média de Temperatura"
            const calculateButton = document.getElementById('calculate-average');
            calculateButton.addEventListener('click', calculateAverage);

            function desenharIcone(status) {
                const statusIcon = document.getElementById("statusIcon");
                statusIcon.innerHTML = "";

                if (status === "conectado") {
                    // Ícone verde
                    const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    circle.setAttribute("cx", 12);
                    circle.setAttribute("cy", 12);
                    circle.setAttribute("r", 10);
                    circle.setAttribute("fill", "green");
                    statusIcon.appendChild(circle);
                } else if (status === "falha") {
                    // Ícone cinza
                    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
                    rect.setAttribute("x", 2);
                    rect.setAttribute("y", 2);
                    rect.setAttribute("width", 20);
                    rect.setAttribute("height", 20);
                    rect.setAttribute("fill", "gray");
                    statusIcon.appendChild(rect);
                } else {
                    console.error("Status inválido: Use 'conectado' ou 'falha'");
                }
            }

            desenharIcone("<?php echo $status; ?>");
        });

</script>

<script>
        // Função para calcular e exibir a contagem de sensores com base no mês selecionado
        function calcularContagemSensores() {
            const selectedMonthInput = document.getElementById("selected-month-iot");
            const sensorCountResult = document.getElementById("sensor-count-result");

            // Obtenha o valor do mês selecionado no formato "YYYY-MM"
            const selectedMonth = selectedMonthInput.value;

            // Verifique se o mês selecionado é igual a "2024-04"
            if (selectedMonth === "") {
                sensorCountResult.textContent = "Total de Sensores Cadastrados: 1 Sensor";
            } else if ((selectedMonth === "2024-04") || (selectedMonth === "2024-03")) {
                sensorCountResult.textContent = "Total de Sensores Cadastrados: 1 Sensor";
            } else {
                sensorCountResult.textContent = "Total de Sensores Cadastrados: Nenhum sensor cadastrado";
            }
        }

        // Adicione um ouvinte de evento ao botão para chamar a função quando for clicado
        document.getElementById("calculate-sensor-count").addEventListener("click", calcularContagemSensores);
</script>