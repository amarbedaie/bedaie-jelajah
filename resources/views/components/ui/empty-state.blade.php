@props(['icon' => 'inbox', 'title', 'description' => null, 'compact' => false])

<div {{ $attributes->merge([
    'class' => 'flex flex-col items-center justify-center rounded-card border border-dashed border-hairline '
        . 'bg-surface text-center ' . ($compact ? 'px-5 py-8' : 'px-6 py-14')
]) }}>
    <div class="flex items-center justify-center rounded-2xl bg-brand-50 {{ $compact ? 'w-11 h-11' : 'w-14 h-14' }}">
        <x-ui.icon :name="$icon" class="{{ $compact ? 'w-5 h-5' : 'w-7 h-7' }} text-brand-500" />
    </div>
    <h2 class="mt-4 {{ $compact ? 'text-base' : 'text-lg' }} font-semibold text-navy-900">{{ $title }}</h2>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm text-ink-muted text-pretty">{{ $description }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">{{ $slot }}</div>
    @endif
</div>
