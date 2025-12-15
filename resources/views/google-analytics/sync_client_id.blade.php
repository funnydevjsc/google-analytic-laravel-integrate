<script>
    (function syncGaClientIdToDataLayer() {
        try {
            const cookies = document.cookie.split('; ');
            const gaCookie = cookies.find(cookie => cookie.startsWith('_ga='));
            if (!gaCookie) {
                return;
            }

            const parts = gaCookie.split('=');
            if (parts.length <= 1) {
                return;
            }

            const cookieValue = decodeURIComponent(parts[1] || '');
            const segments = cookieValue.split('.');
            if (segments.length < 4) {
                return;
            }

            const clientId = segments[segments.length - 2] + '.' + segments[segments.length - 1];
            if (!/^\d+\.\d+$/.test(clientId)) {
                return;
            }

            if (typeof window.dataLayer === 'undefined') {
                window.dataLayer = dataLayer ?? [];
            }
            window.dataLayer.push({
                ga_client_id: clientId
            });
        } catch (e) {
            // ignore
        }
    })();
</script>

@if($userData ?? '')
    <script>
        if (typeof window.dataLayer === 'undefined') { window.dataLayer = dataLayer ?? []; }
        window.dataLayer.push(@json($userData));
    </script>
@endif
