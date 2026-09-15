<div class="space-y-6">
    @forelse ($this->documents as $document)
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <x-badge variant="brand">{{ ucfirst($document->type) }}</x-badge>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $document->title }}</h3>
                    </div>
                    @if ($document->description)
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $document->description }}</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Added {{ $document->created_at->format('M j, Y') }}</p>
                </div>
                <x-button href="{{ route('member.documents.download', $document) }}" target="_blank">Download</x-button>
            </div>
        </x-card>
    @empty
        <x-card>
            <p class="text-sm text-slate-600 dark:text-slate-400">You don't have any documents on file yet.</p>
        </x-card>
    @endforelse
</div>
