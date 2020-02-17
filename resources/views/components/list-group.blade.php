---
items: ForEach
items.text: SingleLine
items.icon: Icon
---
<ul class="list-group">
    @foreach ($items as $item)
        <li class="list-group-item">
            <i class="{{$icon}}"></i>
            {{$text}}
        </li>
    @endforeach
</ul>