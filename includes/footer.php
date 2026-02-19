            </div><!-- .content-wrapper -->
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> SDO ALPAS - Schools Division Office of San Pedro City<br>
                Authority to Travel, Locator and Pass slip Approval System</p>
                <p>Department of Education</p>
            </footer>
        </main>
    </div>

    <script src="<?php echo ADMIN_URL; ?>/assets/js/admin.js"></script>
    <script>
    // Store token for AJAX requests
    const ALPAS_TOKEN = '<?php echo $currentToken ?? ''; ?>';
    
    // Helper to add token to URLs
    function addToken(url) {
        if (!ALPAS_TOKEN) return url;
        const separator = url.includes('?') ? '&' : '?';
        return url + separator + 'token=' + ALPAS_TOKEN;
    }
    
    // Override fetch to add token header
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        options.headers = options.headers || {};
        if (ALPAS_TOKEN) {
            options.headers['X-Auth-Token'] = ALPAS_TOKEN;
        }
        return originalFetch(url, options);
    };
    
    </script>
</body>
</html>
