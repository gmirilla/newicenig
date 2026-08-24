<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Save settings
            </x-filament::button>
        </div>
    </form>

    <div class="fi-section mt-8 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Audit log</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The last 20 changes made to these credentials.</p>

        @if ($this->auditLogs->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No changes have been recorded yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 pr-4">When</th>
                            <th class="py-2 pr-4">By</th>
                            <th class="py-2 pr-4">Changes</th>
                            <th class="py-2">IP address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($this->auditLogs as $log)
                            <tr>
                                <td class="py-2 pr-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $log->created_at->format('M j, Y g:ia') }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $log->user?->name ?? 'Unknown' }}</td>
                                <td class="py-2 pr-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($log->changes as $field => $change)
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                                @if ($field === 'secret_key')
                                                    secret key changed
                                                @else
                                                    {{ $field }}: "{{ $change['from'] }}" &rarr; "{{ $change['to'] }}"
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2 text-gray-500 dark:text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
