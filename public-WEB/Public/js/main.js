$(document).ready(function () {
    // Animación de entrada de la página
    $("body").animate({ opacity: 1 }, 800);

    // Hover animado para el botón
    $("#btn-entrar").hover(
        function () {
            $(this).css("box-shadow", "0 6px 12px rgba(0,0,0,0.2)");
        },
        function () {
            $(this).css("box-shadow", "2px 2px 5px rgba(0,0,0,0.3)");
        }
    );

    // Efecto de clic con retardo para redirigir
    $("#btn-entrar").click(function (e) {
        e.preventDefault();
        let link = $(this).attr("href");
        $(this).fadeOut(300, function () {
            window.location.href = link;
        });
    });

    // Mostrar año actual en el footer
    let year = new Date().getFullYear();
    $("#copy").text(`© ${year} - Todos los derechos reservados`);
});
