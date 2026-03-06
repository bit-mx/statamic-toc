@if(!empty($items))
<nav aria-label="Table of contents" class="toc">
    <ul>
        @foreach($items as $item)
            @include('statamic-toc::partials.item', ['item' => $item])
        @endforeach
    </ul>
</nav>
@endif
