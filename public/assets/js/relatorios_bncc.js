(function ($) {
    // Função para verificar o carregamento do Chart.js
    function checkChartJs() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js não foi carregado. Verifique a conexão com o CDN ou o arquivo local.');
            return false;
        }
        console.log('Chart.js carregado com sucesso. Versão:', Chart.version);
        return true;
    }

    // Função para renderizar o gráfico de barras (Média por Série)
    function renderGraficoMediaSerie() {
        const canvas = document.getElementById('mediaPorSerieChart');
        if (!canvas) return;

        const series = JSON.parse(canvas.dataset.series || '[]');
        const medias = JSON.parse(canvas.dataset.medias || '[]');

        if (series.length === 0 || medias.length === 0) {
            console.warn('Dados insuficientes para renderizar o Gráfico de Média por Série.');
            return;
        }

        if (checkChartJs()) {
            try {
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: series,
                        datasets: [{
                            label: 'Média de Pontuação',
                            data: medias,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 10,
                                title: {
                                    display: true,
                                    text: 'Média (0-10)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Série'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Média de Pontuação por Série'
                            }
                        }
                    }
                });
                console.log('Gráfico de Média por Série renderizado.');
            } catch (e) {
                console.error('Erro ao renderizar Gráfico de Média por Série:', e);
            }
        }
    }

    // Função para carregar a tabela "Alunos com Pontuação Abaixo de 7.0" via AJAX
    function loadAlunosAbaixo7(page = 1) {
        const container = $('#alunos-abaixo-7-content');
        const formulario_id = container.data('formulario-id');

        if (!formulario_id) {
            container.html('<table id="alunos-abaixo-7-table"><thead><tr><th>Nome</th><th>Email</th><th>Série</th><th>Pontuação</th></tr></thead><tbody><tr><td colspan="4">Selecione um formulário para ver os alunos com baixo desempenho.</td></tr></tbody></table>');
            return;
        }

        $.ajax({
            url: 'fetch_alunos_abaixo_7.php',
            method: 'GET',
            data: {
                formulario_id: formulario_id,
                pagina: page
            },
            success: function(response) {
                container.html(response);
                console.log('Tabela "Alunos com Pontuação Abaixo de 7.0" carregada com sucesso.');
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar a tabela "Alunos com Pontuação Abaixo de 7.0":', error);
                container.html('<p>Erro ao carregar os dados. Tente novamente.</p>');
            }
        });
    }

    // Função para exportar CSV
    function exportarCSV() {
        const formulario_id = document.getElementById('formulario_id').value;
        if (!formulario_id) {
            alert('Por favor, selecione um formulário antes de exportar.');
            return;
        }
        window.location.href = 'exportar_relatorio.php?formulario_id=' + encodeURIComponent(formulario_id);
    }

    // Função para toggle da sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('shifted');
        localStorage.setItem('sidebarActive', sidebar.classList.contains('active'));
    }

    // Inicialização
    $(document).ready(function () {
        // Verificar estado da sidebar no carregamento
        if (localStorage.getItem('sidebarActive') === 'true') {
            $('#sidebar').addClass('active');
            $('#main-content').addClass('shifted');
        }

        // Evento para toggle da sidebar
        $('#menu-toggle').on('click', function () {
            toggleSidebar();
        });

        // Evento para toggle do submenu
        $('.sidebar-toggle').on('click', function (e) {
            e.preventDefault();
            const $submenu = $(this).next('.submenu');
            const $toggleIcon = $(this).find('.submenu-toggle');
            $submenu.slideToggle(200);
            $toggleIcon.toggleClass('open');
        });

        // Renderizar gráfico
        renderGraficoMediaSerie();

        // Carregar a tabela "Alunos com Pontuação Abaixo de 7.0" inicialmente
        loadAlunosAbaixo7();

        // Evento para os botões de paginação
        $(document).on('click', '.pagination-btn', function() {
            const page = $(this).data('page');
            loadAlunosAbaixo7(page);
        });

        // Expor exportarCSV globalmente
        window.exportarCSV = exportarCSV;
    });
})(jQuery);