@php
    $widgetType = \App\Models\SystemSetting::get('support_widget_type', 'tawkto');
    $tawkUrl = \App\Models\SystemSetting::get('tawkto_embed_url') ?: env('TAWKTO_EMBED_URL');
    $customUrl = \App\Models\SystemSetting::get('custom_chat_url');
@endphp

@if ($widgetType === 'tawkto' && $tawkUrl)
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function(){
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = @json($tawkUrl);
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
@elseif ($widgetType === 'custom_url' && $customUrl)
    <a href="{{ $customUrl }}" target="_blank" rel="noopener" class="fixed bottom-20 right-4 lg:bottom-6 lg:right-6 z-40 h-14 w-14 rounded-full bg-brand-500 hover:bg-brand-600 text-white shadow-lg flex items-center justify-center">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
    </a>
@endif
