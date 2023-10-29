# winetech

# Documentação do Site WineTech

## Descrição do Projeto

O WineTech é um site desenvolvido no WordPress para atender a um plano de negócios acadêmico. Ele foi projetado para coletar, analisar e apresentar dados provenientes de sensores IoT, que estão conectados a um banco de dados não relacional MongoDB por meio de um plugin personalizado chamado "Winetech Data Analysis" desenvolvido também por nós para o objetivo do TCC. Este README fornece informações sobre como configurar e usar o site WineTech, bem como detalhes sobre o plugin associado.

## Funcionalidades Principais

### 1. Conexão à API IoT MongoDB

O plugin Winetech Data Analysis permite que o site WineTech se conecte a uma API chamada "api-iot-mongodb". Esta API é responsável por recuperar informações em tempo real do banco de dados MongoDB, que armazena dados de sensores IoT relacionados à temperatura e quantidade de vinho, e apresenta um painel de controle que permite aos clientes analisar os dados coletados. O painel oferece as seguintes funcionalidades:
- Visualização em tempo real da temperatura e quantidade de vinho.
- Notificações pré-estabelecidas com base nos parâmetros de temperatura e quantidade consultados pela API.

### 2. Dashboard de Análise de Dados

O WineTech apresenta o dashboard de análise de dados, o site WineTech oferece recursos de gerenciamento de negócios, incluindo:
- Verificação dos tanques de armazenamento de vinho.
- Acompanhamento de lotes de vinho.
- Gerenciamento de usuários do sistema.


## Configuração

### Requisitos

Antes de configurar o WineTech, certifique-se de ter o seguinte instalado:

- Xampp: [Site oficial do Xampp](https://www.apachefriends.org/pt_br/download.html)
- Plugin Winetech Data Analysis. (incluso neste repositório)
- Dump atualisado do banco de dados do projeto. (incluso neste repositório)
- Conexão a internet.

### Instalação

1. Clone este repositório para o diretório raiz do seu servidor web.
2. Instale e ative o plugin "Winetech Data Analysis" no WordPress.
3. Certifique-se de que o banco de dados mysql do wordpress esteja configurado e em execução.

### Uso

1. Acesse o painel de administração do WordPress.
2. Configure as preferências do WineTech, incluindo a API e as notificações pré-estabelecidas.
3. Navegue para a página principal de admin do WineTech para visualizar o dashboard e usar as funcionalidades de gerenciamento de negócios.

## Menu de Como Rodar o WordPress no XAMPP

Se você deseja rodar o WordPress localmente usando o XAMPP, siga os passos a seguir:

1. Faça o download e instale o XAMPP em seu computador: [Download do XAMPP](https://www.apachefriends.org/pt_br/download.html)
2. Inicie o XAMPP e inicie os serviços Apache e MySQL.
3. Copie o diretório do WordPress para a pasta "htdocs" no diretório de instalação do XAMPP.
4. Abra o painel de controle do XAMPP e acesse o phpMyAdmin.
5. importe o banco de dados para o WordPress.
6. Abra um navegador da web e navegue para `http://localhost/nome-do-seu-diretório-wordpress`.

## Licença

Este projeto está sob a licença MIT.

## Contato

Para perguntas ou suporte, entre em contato conosco em `bruno.dev.sousa@outlook.com`




## WP-ADMIN
user: winetech
password: admin123@winetech
