@if($gaPush ?? '')
    <script>
        if (typeof window.dataLayer === 'undefined') { window.dataLayer = dataLayer ?? []; }
        window.dataLayer.push(@json($gaPush));
    </script>
@endif
