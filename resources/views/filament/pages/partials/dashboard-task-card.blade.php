<a class="rd-task rd-tone-{{ $item['tone'] }}" href="{{ $item['url'] ?: '#' }}">
    <div class="rd-task-main">
        <strong>{{ $item['title'] }}</strong>
        <p>{{ $item['description'] }}</p>
        <div class="rd-task-meta">
            <span>{{ $item['status'] }}</span>
            <span>{{ $item['urgency'] }}</span>
            <span>{{ $item['responsavel'] }}</span>
            @if ($item['due'])
                <span>Vence: {{ $item['due'] }}</span>
            @endif
            <span>Parado: {{ $item['stopped_for'] }}</span>
            @if ($item['value'])
                <span>{{ $item['value'] }}</span>
            @endif
            @if ($item['blocked'])
                <span>Bloqueado</span>
            @endif
        </div>
    </div>
</a>
