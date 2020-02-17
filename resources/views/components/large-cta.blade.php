---
heading: SingleLine
paragraph_one: RichText
body: RichText
button_text: SingleLine
button_src: Url
icons: Icon
---
<div class="jumbotron">
    <h1 class="display-4">{{$heading ?? ''}}</h1>
    <p class="lead">{{$paragraph_one ?? ''}}</p>
    <hr class="my-4">
    <p>{{$body ?? ''}}</p>
    <a class="btn btn-primary btn-lg" href="{{$button_src ?? ''}}" role="button">{{$button_text ?? ''}}</a>
</div>