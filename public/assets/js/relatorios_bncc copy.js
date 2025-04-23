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
        const canvas = document.getElementById('graficoMediaSerie');
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
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 10
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

    // Função para renderizar o gráfico de pizza (Distribuição de Respostas)
    function renderGraficoRespostas() {
        const canvas = document.getElementById('graficoRespostas');
        if (!canvas) return;

        const respostas = JSON.parse(canvas.dataset.respostas || '[]');
        const quantidades = JSON.parse(canvas.dataset.quantidades || '[]');

        console.log('Dados para Gráfico de Pizza - Respostas:', respostas);
        console.log('Dados para Gráfico de Pizza - Quantidades:', quantidades);

        if (respostas.length === 0 || quantidades.length === 0) {
            console.warn('Dados insuficientes para renderizar o Gráfico de Pizza.');
            return;
        }

        if (checkChartJs()) {
            try {
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: respostas,
                        datasets: [{
                            data: quantidades,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.raw || 0;
                                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        let percentage = ((value / total) * 100).toFixed(2);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Gráfico de Pizza renderizado.');
            } catch (e) {
                console.error('Erro ao renderizar Gráfico de Pizza:', e);
            }
        }
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

        // Renderizar gráficos
        renderGraficoMediaSerie();
        renderGraficoRespostas();

        // Expor exportarCSV globalmente
        window.exportarCSV = exportarCSV;
    });
})(jQuery);