/* Responsabilidade: Gerencia o toggle da sidebar, persiste seu estado e controla submenus */
$(document).ready(function() {
    // Toggle da sidebar
    if (localStorage.getItem('sidebarActive') === 'true') {
        $('#sidebar').addClass('active');
        $('#content').addClass('shifted');
    }

    $('#menu-toggle').on('click', function() {
        $('#sidebar').addClass('transition-enabled');
        $('#content').addClass('transition-enabled');
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('shifted');
        localStorage.setItem('sidebarActive', $('#sidebar').hasClass('active'));
        setTimeout(function() {
            $('#sidebar').removeClass('transition-enabled');
            $('#content').removeClass('transition-enabled');
        }, 300);
    });

    // Toggle do submenu
    $('.sidebar-toggle').on('click', function(e) {
        e.preventDefault();
        const $submenu = $(this).next('.submenu');
        const $toggleIcon = $(this).find('.submenu-toggle');

        $submenu.slideToggle(200); // Animação suave
        $toggleIcon.toggleClass('open'); // Gira a seta
    });
});
// Função para alternar a sidebar e o conteúdo principal
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('shifted');

    // Atualiza o estado no localStorage
    const isActive = sidebar.classList.contains('active');
    localStorage.setItem('sidebarActive', isActive);
}
