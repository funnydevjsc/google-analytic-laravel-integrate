<script>
    @if($gaPush)
    if (typeof window.dataLayer === 'undefined') { window.dataLayer = dataLayer ?? []; }
    window.dataLayer.push(@json($gaPush));
    @endif
</script>