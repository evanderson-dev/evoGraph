/* Responsabilidade: Gerencia o modal de detalhes do aluno */
$(document).ready(function() {
    $(document).on('click', '#modal-details-aluno .close-modal-btn', function() {
        $('#modal-details-aluno').css('display', 'none');
    });
});

$(document).on('click', '.aluno-row', function(e) {
    if (!$(e.target).hasClass('action-btn') && !$(e.target).parent().hasClass('action-btn')) {
        const matricula = $(this).data('matricula');

        $.ajax({
            url: 'fetch_aluno.php',
            method: 'POST',
            data: { action: 'fetch_aluno', matricula: matricula, context: 'details' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const aluno = response.aluno;
                    const nomeCompleto = `${aluno.nome} ${aluno.sobrenome}`;
                    const dataNascimento = aluno.data_nascimento || 'N/A';
                    const dataMatricula = aluno.data_matricula || 'N/A';
                    const turmaNome = aluno.turma_nome || 'Sem turma';
                    const fotoSrc = aluno.foto || 'img/default-photo.jpg';
                    const nomePai = aluno.nome_pai || 'N/A';
                    const nomeMae = aluno.nome_mae || 'N/A';
                    const email = aluno.email || 'N/A';

                    const content = `
                        <h2 class="modal-title">Detalhes do Aluno</h2>
                        <div class="details-form">
                            <div class="form-group foto-placeholder">
                                <label>Foto do Aluno</label>
                                <div class="foto-box">
                                    <img id="detalhes-foto" src="${fotoSrc}" alt="Foto do Aluno">
                                </div>
                            </div>
                            <div class="form-group info-right">
                                <label for="detalhes-nome">Nome:</label>
                                <input type="text" id="detalhes-nome" value="${nomeCompleto}" readonly>
                                <label for="detalhes-matricula">Matrícula:</label>
                                <input type="text" id="detalhes-matricula" value="${aluno.matricula}" readonly>
                                <label for="detalhes-turma">Turma:</label>
                                <input type="text" id="detalhes-turma" value="${turmaNome}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="detalhes-nascimento">Data de Nascimento:</label>
                                <input type="text" id="detalhes-nascimento" value="${dataNascimento}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="detalhes-data-matricula">Data de Matrícula:</label>
                                <input type="text" id="detalhes-data-matricula" value="${dataMatricula}" readonly>
                            </div>
                            <div class="form-group full-width">
                                <label for="detalhes-email">E-mail:</label>
                                <input type="text" id="detalhes-email" value="${email}" readonly>
                            </div>
                            <div class="form-group full-width">
                                <label for="detalhes-pai">Nome do Pai:</label>
                                <input type="text" id="detalhes-pai" value="${nomePai}" readonly>
                            </div>
                            <div class="form-group full-width">
                                <label for="detalhes-mae">Nome da Mãe:</label>
                                <input type="text" id="detalhes-mae" value="${nomeMae}" readonly>
                            </div>
                            <div class="modal-buttons">
                                <button class="btn close-modal-btn">Fechar</button>
                            </div>
                        </div>
                    `;
                    $('#modal-details-aluno .modal-content').html(content);
                    $('#modal-details-aluno').css('display', 'block');
                } else {
                    alert('Erro ao carregar detalhes do aluno: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Erro AJAX em fetch_aluno.php:', xhr.statusText);
                alert('Erro ao comunicar com o servidor: ' + xhr.statusText);
            }
        });
    }
});