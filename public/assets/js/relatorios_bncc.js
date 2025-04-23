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
                const mediaPorSerieChart = new Chart(ctx, {
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

    // Função para renderizar o gráfico de barras agrupadas (Percentual de Acertos por Série)
    function renderGraficoPercentualSerie() {
        const canvas = document.getElementById('percentualPorSerieChart');
        if (!canvas) return;

        const series = JSON.parse(canvas.dataset.series || '[]');
        const perguntas = JSON.parse(canvas.dataset.perguntas || '[]');
        const percentuais = JSON.parse(canvas.dataset.percentuais || '{}');

        if (series.length === 0 || perguntas.length === 0 || Object.keys(percentuais).length === 0) {
            console.warn('Dados insuficientes para renderizar o Gráfico de Percentual por Série.');
            return;
        }

        if (checkChartJs()) {
            try {
                const ctx = canvas.getContext('2d');
                const datasets = series.map((serie, index) => {
                    const colors = [
                        'rgba(54, 162, 235, 0.6)',  // Azul
                        'rgba(255, 99, 132, 0.6)',  // Vermelho
                        'rgba(75, 192, 192, 0.6)',  // Verde
                        'rgba(255, 205, 86, 0.6)',  // Amarelo
                        'rgba(153, 102, 255, 0.6)'  // Roxo
                    ];
                    return {
                        label: `Série ${serie}`,
                        data: perguntas.map(pergunta => percentuais[pergunta][serie] || 0),
                        backgroundColor: colors[index % colors.length],
                        borderColor: colors[index % colors.length].replace('0.6', '1'),
                        borderWidth: 1
                    };
                });

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: perguntas,
                        datasets: datasets
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Percentual de Acertos (%)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Pergunta'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Percentual de Acertos por Série'
                            }
                        }
                    }
                });
                console.log('Gráfico de Percentual por Série renderizado.');
            } catch (e) {
                console.error('Erro ao renderizar Gráfico de Percentual por Série:', e);
            }
        }
    }

    // Função para renderizar o gráfico de barras horizontais (Alunos com Pontuação Abaixo de 7.0)
    function renderGraficoAlunosAbaixo7() {
        const canvas = document.getElementById('alunosAbaixo7Chart');
        if (!canvas) return;

        const series = JSON.parse(canvas.dataset.series || '[]');
        const quantidades = JSON.parse(canvas.dataset.quantidades || '[]');

        if (series.length === 0 || quantidades.length === 0) {
            console.warn('Dados insuficientes para renderizar o Gráfico de Alunos Abaixo de 7.0.');
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
                            label: 'Número de Alunos',
                            data: quantidades,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y', // Torna o gráfico horizontal
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Número de Alunos'
                                }
                            },
                            y: {
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
                                text: 'Alunos com Pontuação Abaixo de 7.0 por Série'
                            }
                        }
                    }
                });
                console.log('Gráfico de Alunos Abaixo de 7.0 renderizado.');
            } catch (e) {
                console.error('Erro ao renderizar Gráfico de Alunos Abaixo de 7.0:', e);
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
        renderGraficoPercentualSerie();
        renderGraficoAlunosAbaixo7();

        // Expor exportarCSV globalmente
        window.exportarCSV = exportarCSV;
    });
})(jQuery);