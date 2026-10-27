@php
    $hero = \App\Support\SiteContent::data('about', 'hero');
    $story = \App\Support\SiteContent::data('about', 'story');
    $advantagesIntro = \App\Support\SiteContent::data('about', 'advantages_intro');
    $advantages = \App\Support\SiteContent::data('about', 'advantages');
    $teamIntro = \App\Support\SiteContent::data('about', 'team_intro');
    $team = \App\Support\SiteContent::data('about', 'team');
    $stats = \App\Support\SiteContent::data('about', 'stats');
    $storyImage = \App\Support\SiteContent::imageUrl($story['image'] ?? null);
    $advantageIcons = [
        'M13 10V3L4 14h7v7l9-11h-7z',
        'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    ];
@endphp
<x-public-layout :title="$page->meta_title ?: $page->title" :description="$page->meta_description">
    <section class="max-w-5xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-charcoal-900 dark:text-white leading-tight">
                {{ $hero['title'] }}
                @if ($hero['title_highlight'] !== '')
                    <span class="text-brand-600">{{ $hero['title_highlight'] }}</span>
                @endif
            </h1>
            @if ($hero['intro'] !== '')
                <p class="mt-4 text-charcoal-500 dark:text-charcoal-400">{!! nl2br(e($hero['intro'])) !!}</p>
            @endif
        </div>

        <div class="mt-12 grid lg:grid-cols-2 gap-6 items-stretch">
            <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm">
                <h2 class="font-bold text-charcoal-900 dark:text-white flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    {{ $story['heading'] }}
                </h2>
                <div class="prose dark:prose-invert max-w-none mt-4 text-sm text-charcoal-600 dark:text-charcoal-300 leading-relaxed [&_p]:mb-4">
                    {!! $page->content !!}
                </div>
            </div>

            <div class="relative rounded-2xl overflow-hidden shadow-sm min-h-[260px] bg-gradient-to-br from-brand-600 to-charcoal-900 flex items-end p-6">
                @if ($storyImage)
                    <img src="{{ $storyImage }}" alt="{{ $story['caption'] }}" class="absolute inset-0 h-full w-full object-cover">
                @endif
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.15),transparent_60%)]"></div>
                @if ($story['caption'] !== '')
                    <span class="relative text-white text-xs font-semibold tracking-widest uppercase bg-black/20 px-3 py-1.5 rounded-full">{{ $story['caption'] }}</span>
                @endif
            </div>
        </div>

        @if ($advantagesIntro['title'] !== '' || $advantagesIntro['subtitle'] !== '' || count($advantages) > 0)
            <div class="mt-16 text-center">
                @if ($advantagesIntro['title'] !== '')
                    <h2 class="text-2xl sm:text-3xl font-bold text-charcoal-900 dark:text-white">{{ $advantagesIntro['title'] }}</h2>
                @endif
                @if ($advantagesIntro['subtitle'] !== '')
                    <p class="mt-2 text-charcoal-500 dark:text-charcoal-400">{!! nl2br(e($advantagesIntro['subtitle'])) !!}</p>
                @endif
            </div>

            @if (count($advantages) > 0)
                <div class="mt-8 grid sm:grid-cols-3 gap-6">
                    @foreach ($advantages as $index => $advantage)
                        @php
                            $icon = $advantageIcons[$index % count($advantageIcons)];
                            $advantageImage = \App\Support\SiteContent::imageUrl($advantage['image'] ?? null);
                        @endphp
                        <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 bg-white dark:bg-charcoal-900 p-6 shadow-sm text-left">
                            @if ($advantageImage)
                                <img src="{{ $advantageImage }}" alt="" class="h-10 w-10 rounded-full object-cover mb-3">
                            @else
                                <div class="h-10 w-10 rounded-full bg-brand-50 dark:bg-charcoal-800 text-brand-500 flex items-center justify-center mb-3">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                                </div>
                            @endif
                            <h3 class="font-semibold text-charcoal-900 dark:text-white">{{ $advantage['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm text-charcoal-500 dark:text-charcoal-400">{!! nl2br(e($advantage['description'] ?? '')) !!}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        @if ($teamIntro['title'] !== '' || $teamIntro['subtitle'] !== '' || count($team) > 0)
            <div class="mt-16">
                @if ($teamIntro['title'] !== '')
                    <h2 class="text-2xl sm:text-3xl font-bold text-charcoal-900 dark:text-white">{{ $teamIntro['title'] }}</h2>
                @endif
                @if ($teamIntro['subtitle'] !== '')
                    <p class="mt-2 text-charcoal-500 dark:text-charcoal-400">{!! nl2br(e($teamIntro['subtitle'])) !!}</p>
                @endif
                @if (count($team) > 0)
                    <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-6">
                        @foreach ($team as $member)
                            @php $photo = \App\Support\SiteContent::imageUrl($member['photo'] ?? null); @endphp
                            <div>
                                <div class="aspect-square rounded-2xl bg-charcoal-100 dark:bg-charcoal-800 flex items-center justify-center overflow-hidden">
                                    @if ($photo)
                                        <img src="{{ $photo }}" alt="{{ $member['name'] ?? '' }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-16 w-16 text-charcoal-300 dark:text-charcoal-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.418 0-8 2.239-8 5v1a1 1 0 001 1h14a1 1 0 001-1v-1c0-2.761-3.582-5-8-5z" /></svg>
                                    @endif
                                </div>
                                <p class="mt-3 font-semibold text-sm text-charcoal-900 dark:text-white">{{ $member['name'] ?? '' }}</p>
                                <p class="text-xs text-charcoal-400">{{ $member['role'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if (count($stats) > 0)
            <div class="mt-16 grid sm:grid-cols-3 gap-6 text-center">
                @foreach ($stats as $stat)
                    <div class="rounded-2xl border border-charcoal-100 dark:border-charcoal-800 p-6">
                        <p class="text-2xl font-extrabold text-brand-600">{{ $stat['value'] ?? '' }}</p>
                        <p class="text-xs text-charcoal-400 mt-1">{{ $stat['label'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-public-layout>
