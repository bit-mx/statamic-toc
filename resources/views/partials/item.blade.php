<li>
    <a href="{{ $item['url'] ?? '#'.$item['id'] }}">{{ $item['text'] }}</a>

    @if(!empty($item['children']))
        <ul>
            @foreach($item['children'] as $child)
                @include('statamic-toc::partials.item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
