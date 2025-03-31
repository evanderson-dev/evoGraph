/* Responsabilidade: Gerencia o toggle da sidebar e persiste seu estado. */
$(document).ready(function() {
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
});