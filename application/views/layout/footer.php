    </div>
</div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
$(document).ready(function() {
    // Sidebar toggle for desktop and mobile
    $('#sidebarCollapse, #sidebarOverlay').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
        $('#sidebarOverlay').toggleClass('active');
        
        // Change icon
        if ($('#sidebar').hasClass('active')) {
            $('#sidebarCollapse i').removeClass('fa-bars').addClass('fa-times');
        } else {
            $('#sidebarCollapse i').removeClass('fa-times').addClass('fa-bars');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Close sidebar on mobile when clicking a link
    if ($(window).width() <= 991) {
        $('#sidebar ul li a').on('click', function() {
            $('#sidebar').removeClass('active');
            $('#content').removeClass('active');
            $('#sidebarOverlay').removeClass('active');
            $('#sidebarCollapse i').removeClass('fa-times').addClass('fa-bars');
        });
    }
    
    // Handle window resize
    $(window).resize(function() {
        if ($(window).width() > 991) {
            $('#sidebar').removeClass('active');
            $('#content').removeClass('active');
            $('#sidebarOverlay').removeClass('active');
            $('#sidebarCollapse i').removeClass('fa-times').addClass('fa-bars');
        }
    });
});
</script>

</body>
</html>