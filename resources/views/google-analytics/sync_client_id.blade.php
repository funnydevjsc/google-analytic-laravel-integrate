@if($clientId ?? '')
    <script>
        function updateGAClientId(newClientId) {
            const cookies = document.cookie.split('; ');
            let gaCookie = cookies.find(cookie => cookie.startsWith('_ga='));
            if (!gaCookie) {
                return;
            }
            const parts = gaCookie.split('=');
            if (parts.length <= 1) {
                return;
            }
            const cookieValue = parts[1];
            const newGaCookieValue = cookieValue.replace(/(GA\d+\.\d+\.)\d+\.\d+/, `$1${newClientId}`);
            document.cookie = `_ga=${newGaCookieValue}; path=/; SameSite=Lax`;
        }
        updateGAClientId('{{ $clientId }}');
    </script>
@endif

@if($userData ?? '')
    <script>
        if (typeof window.dataLayer === 'undefined') { window.dataLayer = dataLayer ?? []; }
        window.dataLayer.push(@json($userData));
    </script>
@endif
