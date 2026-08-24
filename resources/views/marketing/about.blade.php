<x-layouts.marketing title="About">
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            eyebrow="About ICEN"
            heading="Setting the standard for economic practice in Nigeria"
        />

        <div class="prose prose-gray mt-10 max-w-none dark:prose-invert">
            @if ($page?->body)
                {!! $page->body !!}
            @else
                <p>
                    The Institute of Chartered Economists of Nigeria (ICEN) is the professional body responsible for
                    certifying, developing, and representing economists across Nigeria. Since our founding, we have
                    worked to establish rigorous standards of practice, provide continuing professional development,
                    and advocate for the economics profession at every level of industry and government.
                </p>
                <p>
                    Our members work across banking, government, academia, consulting, and industry — united by a
                    shared commitment to professional excellence and ethical practice.
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    This page is placeholder content. Edit it from the admin panel under Content → Pages (slug: <code>about</code>).
                </p>
            @endif
        </div>
    </section>
</x-layouts.marketing>
